<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "login_user".
 *
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string|null $authKey
 * @property string|null $role
 */
class LoginUser extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'login_user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            [['last_login'], 'safe'],
            [['username', 'password'], 'string', 'max' => 128],
            [['authKey'], 'string', 'max' => 200],
            [['role'], 'string', 'max' => 20],
            [['username'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'password' => 'Password',
            'authKey' => 'Auth Key',
            'role' => 'Role',
            'last_login' => 'Last Login',
        ];
    }
}
