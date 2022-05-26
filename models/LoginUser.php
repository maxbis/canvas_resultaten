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

    public $old_password;
	public $new_password;
	public $repeat_password;

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
            ['old_password, new_password, repeat_password', 'required', 'on' => 'changePwd'],
		    ['old_password', 'findPasswords', 'on' => 'changePwd'],
		    ['repeat_password', 'compare', 'compareAttribute'=>'new_password', 'on'=>'changePwd'],
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
    
	//matching the old password with your existing password.
	public function findPasswords($attribute, $params)
	{
		$user = User::model()->findByPk(Yii::app()->user->id);
		if ($user->password != md5($this->old_password))
			$this->addError($attribute, 'Old password is incorrect.');
	}

}
