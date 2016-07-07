<?php

namespace app\controllers;

use yii\data\Pagination;
use app\models\ItemExtended;
use app\models\ItemReading;
use app\models\ItemReadingGroup;
use yii\helpers\ArrayHelper;

class TableController extends Controller {
    public function behaviors() {
        return ArrayHelper::merge([
            'verbs' => [
                'class' => \yii\filters\VerbFilter::className(),
                'actions' => [
                    'ajax-create'  => ['post'],
                ],
            ],
        ], parent::behaviors());
    }
    public function actionIndex() {
        $query = ItemExtended::find()->where(['parent_id' => null]);
        $pagination = new Pagination([
            'defaultPageSize' => 5,
            'totalCount' => $query->count(),
        ]);
        $items = $query->orderBy('name')
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        return $this->render('index.twig', [
            'items' => $items,
            'pagination' => $pagination,
        ]);
    }
    public function actionAjaxCreate() {
        $request = \Yii::$app->request;
        $group = new ItemReadingGroup();
        $group->date_range = $request->post('date_range', null);
        if (!$group->validate())
            throw new \yii\web\HttpException(422, \yii\helpers\Json::encode($group->errors));
        $connection = \Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            if (!$group->save(false))
                throw new \yii\web\HttpException(500, \Yii::t('app', 'Could not save group'));
            $readings = $request->post('ItemReading', []);
            foreach ($readings as $item_id => $item_count) {
                $itemReading = new ItemReading();
                $itemReading->item_id = $item_id;
                $itemReading->count = $item_count;
                $itemReading->item_reading_group_id = $group->id;
                if (!$itemReading->save(false))
                    throw new \yii\web\HttpException(500, \Yii::t('app', 'Could not save reading'));
            }
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollback();
        }
    }
    public function actionView($id)
    {
        $query = ItemExtended::find(['root_id' => $id])->asArray();
        $table = $this->addTreeInfo(ArrayHelper::index($query->orderBy('level,path')->all(), 'id'));
        //
        $query = ItemReadingGroup::find()->asArray() // Yii2 bug forces to do this!?
            ->with(['itemReadingsExtended' => function($q) use ($id) {
                $q->where(['root_id' => $id]);
            }]);
        $pagination = new Pagination([
            'defaultPageSize' => 10,
            'totalCount' => $query->count()
        ]);
        $groups = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();
        $table = ArrayHelper::index($table, 'id', 'level');
        $root = array_shift($table);
        $table_title = array_values($root)[0]['name'];

        $group_model = new ItemReadingGroup();
        $items = ItemExtended::findLeaves($id)->orderBy('path')->asArray()->all();

        return $this->render('view.twig', [
            'table_title' => $table_title,
            'groups' => $groups,
            'items' => $items,
            'n_levels' => 4,//$root['n_levels'],
            'group_model' => $group_model,
            'pagination' => $pagination,
            'table' => $table,
        ]);
    }
    protected function addTreeInfo($table) {
        $parents = [];
        $noLeaves = [];
        $maxLevel = 0;
        foreach (array_reverse($table) as $v) {
            $parents[] = $v['parent_id'];
            if ($maxLevel < $v['level']) $maxLevel = $v['level'];
            if (array_search($v['id'], $parents, true) !== false) {
                if (!isset($noLeaves[$v['parent_id']])) $noLeaves[$v['parent_id']] = $noLeaves[$v['id']];
                else $noLeaves[$v['parent_id']] += $noLeaves[$v['id']];
                $levelsBelow[$v['parent_id']] = $levelsBelow[$v['id']] + 1;
            } else {
                $noLeaves[$v['id']] = 1;
                if (!isset($noLeaves[$v['parent_id']])) $noLeaves[$v['parent_id']] = 1;
                else $noLeaves[$v['parent_id']] += 1;
                $levelsBelow[$v['parent_id']] = 1;
                $levelsBelow[$v['id']] = 0;
            }
        }
        foreach ($table as &$v) {
            $v['no_leaves'] = $noLeaves[$v['id']];
            if (array_search($v['id'], $parents, true) === false)
                $v['span_levels'] = $maxLevel - $levelsBelow[$v['id']] - $v['level'] + 1;
        }
        return $table;
    }
}
