<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "contact".
 *
 * @property integer $id
 * @property string $reference
 * @property string $first_name
 * @property string $last_name
 * @property string $nationality
 * @property string $type_of_data
 * @property string $contact_source
 * @property string $internet
 * @property string $birth_date
 * @property string $country_of_residence
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Transaction[] $transactions
 * @property Transaction[] $transactions0
 */
class Contact extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'contact';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['reference', 'created_at', 'updated_at'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['reference', 'birth_date'], 'string', 'max' => 20],
            [['first_name', 'last_name', 'nationality', 'type_of_data', 'contact_source', 'internet', 'country_of_residence'], 'string', 'max' => 42],
            [['reference'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'reference' => Yii::t('app', 'Reference'),
            'first_name' => Yii::t('app', 'First Name'),
            'last_name' => Yii::t('app', 'Last Name'),
            'nationality' => Yii::t('app', 'Nationality'),
            'type_of_data' => Yii::t('app', 'Type Of Data'),
            'contact_source' => Yii::t('app', 'Contact Source'),
            'internet' => Yii::t('app', 'Internet'),
            'birth_date' => Yii::t('app', 'Birth Date'),
            'country_of_residence' => Yii::t('app', 'Country Of Residence'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransactions()
    {
        return $this->hasMany(Transaction::className(), ['buyer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransactions0()
    {
        return $this->hasMany(Transaction::className(), ['seller_id' => 'id']);
    }
}