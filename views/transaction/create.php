<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\TransactionListItem */

$this->title = Yii::t('app', 'Create Transaction');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Transactions'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="transaction-list-item-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_simple_form', [
        'model' => $model,
    ]) ?>

</div>
