<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use app\models\TransactionListItem;

/**
 * TransactionListItemSearch represents the model behind the search form about `app\models\TransactionListItem`.
 */
class TransactionListItemSearch extends TransactionListItem
{
    public $option_signed_from;
    public $option_signed_to;
    public $invoiced_from;
    public $invoiced_to;
    public $search_any;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['transaction_type', 'advisors', 'option_signed_from', 'option_signed_to', 'search_any', 'invoiced_from', 'invoiced_to'], 'safe'],
            [['approved_by_direction', 'approved_by_accounting', 'payrolled', 'invoiced', 'with_collaborator'], 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'option_signed_from' => Yii::t('app', 'Option Signed From'),
            'option_signed_to' => Yii::t('app', 'Option Signed To'),
            'invoiced_from' => Yii::t('app', 'Invoiced From'),
            'invoiced_to' => Yii::t('app', 'Invoiced To'),
            'search_any' => Yii::t('app', 'Search Any'),
            'payrolled' => Yii::t('app', 'Payrolled'),
            'with_collaborator' => Yii::t('app', 'Collaborator'),
            'invoiced' => Yii::t('app', 'Invoiced'),
            'advisors' => Yii::t('app', 'Advisor')
        ]);
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = TransactionListItem::find();

        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'defaultOrder' => ['updated_at' => SORT_DESC]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if ($this->option_signed_from and !$this->option_signed_to)
            $this->option_signed_to = date('Y-m-d');
        if (!$this->option_signed_from and $this->option_signed_to)
            $this->option_signed_from = '1970-01-01';

        if ($this->invoiced_from and !$this->invoiced_to)
            $this->invoiced_to = date('Y-m-d');
        if (!$this->invoiced_from and $this->invoiced_to)
            $this->invoiced_from = '1970-01-01';

        $query->andFilterWhere(
            ['between', 'option_signed_at', $this->option_signed_from, $this->option_signed_to]);
        if ($this->invoiced_from) $query->innerJoinWith(['invoices' => function($q) {
            $q->where(['between', 'issued_at', $this->invoiced_from, $this->invoiced_to]);
        }]);

        //\yii\helpers\VarDumper::dump($this->transaction_type, 9, true); die;
        $query->andFilterWhere(['like', 'advisors', $this->advisors]);
        $query->andFilterWhere([
            'transaction_type' => $this->transaction_type,
            'approved_by_accounting' => $this->approved_by_accounting,
            'approved_by_direction'=> $this->approved_by_direction,
            'payrolled' => $this->payrolled,
            'invoiced' => $this->invoiced,
            'with_collaborator' => $this->with_collaborator
        ]);

        $search_id = null;
        if (substr($this->search_any, 0, 1) == '#' && intval(substr($this->search_any, 1))) $search_id = intval(substr($this->search_any, 1));
        $query->andFilterWhere(['or', 
            ['transaction_list_item.id' => $search_id],
            ['external_id' => $this->search_any],
            ['ilike', 'custom_type', $this->search_any],
            ['ilike', 'transfer_type', $this->search_any],
            ['ilike', 'development_type', $this->search_any],
            ['ilike', 'buyer_provider', $this->search_any],
            ['ilike', 'seller_provider', $this->search_any],
            ['ilike', 'lead_type', $this->search_any],
            ['ilike', 'comments', $this->search_any],
            ['ilike', 'property_location', $this->search_any],
            ['ilike', 'property_building_complex', $this->search_any],
            ['property_reference' => $this->search_any],
            ['seller_reference' => $this->search_any],
            ['ilike', 'seller_name', $this->search_any],
            ['buyer_reference' => $this->search_any],
            ['ilike', 'buyer_name', $this->search_any],
            ['ilike', 'advisors', $this->search_any]]);

        return $dataProvider;
    }
}
