<?php

namespace backend\modules\auth\models;

use backend\modules\auth\Acl;
use backend\modules\auth\Constants;
use backend\modules\conf\Constants as ConfConstants;
use backend\modules\conf\models\EmailQueue;
use backend\modules\conf\models\EmailTemplate;
use backend\modules\conf\models\Timezone;
use common\helpers\FileManager;
use common\helpers\Lang;
use common\helpers\Utils;
use common\models\ActiveSearchInterface;
use common\models\ActiveSearchTrait;
use nenad\passwordStrength\StrengthValidator;
use Yii;
use yii\imagine\Image;
use yii\web\ForbiddenHttpException;
use yii2tech\authlog\AuthLogIdentityBehavior;

/**
 * This is the model class for table "auth_users".
 *
 * @property integer $id
 * @property string $name
 * @property string $username
 * @property string $email
 * @property integer $status
 * @property string $timezone
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $auth_key
 * @property string $account_activation_token
 * @property integer $level_id
 * @property integer $role_id
 * @property string $profile_image
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property string $last_login
 * @property string $require_password_change
 *
 * @property Roles $role
 */
class Users extends UserIdentity implements ActiveSearchInterface
{
    use ActiveSearchTrait;

    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_NOT_ACTIVE = 2;
    const STATUS_BLOCKED = 3;

    public $currentPassword;
    public $password;

    /**
     * confirm password
     * @var string
     */
    public $confirm;
    /**
     *
     * @var bool
     */
    public $send_email = false;

    /**
     * @var
     */
    public $temp_profile_image;

    const SCENARIO_CREATE = 'create';
    const SCENARIO_CHANGE_PASSWORD = 'changePassword';
    const SCENARIO_RESET_PASSWORD = 'resetPassword';
    const SCENARIO_SIGNUP = 'signup';

    const UPLOADS_DIR = 'users';

    /**
     * Initializes the object.
     * This method is called at the end of the constructor.
     * The default implementation will trigger an [[EVENT_INIT]] event.
     * If you override this method, make sure you call the parent implementation at the end
     * to ensure triggering of the event.
     */
    public function init()
    {
        if (empty($this->timezone)) {
            /* @var $setting \common\components\Setting */
            $setting = Yii::$app->setting;
            $this->timezone = $setting->get(ConfConstants::SECTION_SYSTEM, ConfConstants::KEY_DEFAULT_TIMEZONE, Timezone::DEFAULT_TIME_ZONE);
        }

        parent::init();
    }


    public function behaviors()
    {
        return [
            'authLog' => [
                'class' => AuthLogIdentityBehavior::className(),
                'authLogRelation' => 'authLogs',
                'defaultAuthLogData' => function ($model) {
                    return [
                        'ip' => $_SERVER['REMOTE_ADDR'],
                        'host' => @gethostbyaddr($_SERVER['REMOTE_ADDR']),
                        'url' => $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
                        'userAgent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null,
                    ];
                },
            ],
        ];
    }

    public function getAuthLogs()
    {
        return $this->hasMany(AuthLog::className(), ['userId' => 'id']);
    }

    public function fields()
    {
        $fields = parent::fields();

        // remove fields that contain sensitive information
        unset($fields['auth_key'], $fields['password_hash'], $fields['password_reset_token'], $fields['account_activation_token']);

        return $fields;
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'email', 'phone'], 'filter', 'filter' => 'trim'],
            [['name', 'username', 'email', 'level_id', 'phone'], 'required'],
            ['email', 'email'],
            [['level_id', 'role_id'], 'integer'],
            ['username', 'string', 'min' => 2, 'max' => 255],
            [['name', 'profile_image'], 'string', 'max' => 128],
            ['username', 'string', 'min' => 4, 'max' => 30],
            // password field is required on 'create' scenario
            ['password', 'required', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_CHANGE_PASSWORD]],
            [['confirm'], 'compare', 'compareAttribute' => 'password', 'on' => [self::SCENARIO_CHANGE_PASSWORD, self::SCENARIO_CREATE, self::SCENARIO_RESET_PASSWORD, self::SCENARIO_SIGNUP], 'message' => Lang::t('Passwords do not match.')],
            // use passwordStrengthRule() method to determine password strength
            $this->passwordStrengthRule(),
            ['username', 'unique', 'message' => 'This username has already been taken.'],
            ['email', 'unique', 'message' => 'This email address has already been taken.'],
            [['timezone'], 'string', 'max' => 60],
            [['send_email', 'temp_profile_image'], 'safe'],
            [['phone'], 'string', 'max' => 15],
            [['currentPassword'], 'required', 'on' => self::SCENARIO_CHANGE_PASSWORD],
            [['currentPassword'], 'validateCurrentPassword', 'on' => self::SCENARIO_CHANGE_PASSWORD],
            [['status', 'username', 'email', 'phone'], 'safe', 'on' => self::SCENARIO_SEARCH],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Lang::t('Name'),
            'username' => Lang::t('Username'),
            'email' => Lang::t('Email'),
            'phone' => Lang::t('Mobile'),
            'status' => Lang::t('Status'),
            'timezone' => Lang::t('Timezone'),
            'password' => Lang::t('Password'),
            'currentPassword' => Lang::t('Current Password'),
            'level_id' => Lang::t('Level'),
            'role_id' => Lang::t('Role'),
            'profile_image' => Lang::t('Profile Image'),
            'created_at' => Lang::t('Created At'),
            'created_by' => Lang::t('Created By'),
            'updated_at' => Lang::t('Updated At'),
            'last_login' => Lang::t('Last Login'),
            'send_email' => Lang::t('Email the login details to the user.'),
        ];
    }

    /**
     * Set password rule based on our setting value (Force Strong Password).
     *
     * @return array Password strength rule.
     */
    private function passwordStrengthRule()
    {
        // get setting value for 'Force Strong Password'
        $fsp = Yii::$app->params['fsp'];

        // password strength rule is determined by StrengthValidator
        // presets are located in: vendor/nenad/yii2-password-strength/presets.php
        $strong = [['password'], StrengthValidator::className(), 'preset' => 'normal'];

        // normal yii rule
        $normal = ['password', 'string', 'min' => 6];

        // if 'Force Strong Password' is set to 'true' use $strong rule, else use $normal rule
        return ($fsp) ? $strong : $normal;
    }

    /**
     * Finds user by username.
     *
     * @param  string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by email.
     *
     * @param  string $email
     * @return static|null
     */
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token.
     *
     * @param  string $token Password reset token.
     * @return null|static
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds user by account activation token.
     *
     * @param  string $token Account activation token.
     * @return static|null
     */
    public static function findByAccountActivationToken($token)
    {
        return static::findOne([
            'account_activation_token' => $token,
            'status' => self::STATUS_NOT_ACTIVE,
        ]);
    }

    /**
     * Checks to see if the given user exists in our database.
     * If LoginForm scenario is set to lwe (login with email), we need to check
     * user's email and password combo, otherwise we check username/password.
     * NOTE: used in LoginForm model.
     *
     * @param  string $username Can be either username or email based on scenario.
     * @param  string $password
     * @param  string $scenario
     * @return bool|static
     */
    public static function userExists($username, $password, $scenario)
    {
        // if scenario is 'lwe', we need to check email, otherwise we check username
        $field = ($scenario === 'lwe') ? 'email' : 'username';

        if ($user = static::findOne([$field => $username])) {
            if ($user->validatePassword($password)) {
                return $user;
            } else {
                return false; // invalid password
            }
        } else {
            return false; // invalid username|email
        }
    }

    /**
     * @param $status
     * @return string
     */
    public static function decodeStatus($status)
    {
        $decoded = $status;
        switch ($status) {
            case self::STATUS_DELETED:
                $decoded = Lang::t('Deleted');
                break;
            case self::STATUS_ACTIVE:
                $decoded = Lang::t('Active');
                break;
            case self::STATUS_NOT_ACTIVE:
                $decoded = Lang::t('Inactive');
                break;
            case self::STATUS_BLOCKED:
                $decoded = Lang::t('Blocked');
                break;
        }

        return $decoded;
    }

    /**
     * Status options that can be used in dropdown list
     * @return array
     */
    public static function statusOptions()
    {
        return [
            self::STATUS_ACTIVE => static::decodeStatus(self::STATUS_ACTIVE),
            self::STATUS_NOT_ACTIVE => static::decodeStatus(self::STATUS_NOT_ACTIVE),
            self::STATUS_BLOCKED => static::decodeStatus(self::STATUS_BLOCKED),
        ];
    }


    /**
     * Finds out if password reset token is valid.
     *
     * @param  string $token Password reset token.
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int)substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * Generates new password reset token.
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token.
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * Generates new account activation token.
     */
    public function generateAccountActivationToken()
    {
        $this->account_activation_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes account activation token.
     */
    public function removeAccountActivationToken()
    {
        $this->account_activation_token = null;
    }

    /**
     * Search params for the active search
     * ```php
     *   return [
     *       ["name","_searchField","AND|OR"],//default is AND only include this param if there is a need for OR condition
     *       'id',
     *       'email'
     *   ];
     * ```
     * @return array
     */
    public function searchParams()
    {
        return [
            ['email', 'email'],
            ['name', 'name'],
            ['username', 'username'],
            'id',
            'status',
            'level_id',
            'role_id',
            'created_at',
            'created_by',
            'updated_at',
            'last_login',
        ];
    }

    /**
     * This method is called at the beginning of inserting or updating a record.
     * The default implementation will trigger an [[EVENT_BEFORE_INSERT]] event when `$insert` is true,
     * or an [[EVENT_BEFORE_UPDATE]] event if `$insert` is false.
     * When overriding this method, make sure you call the parent implementation like the following:
     *
     * ```php
     * public function beforeSave($insert)
     * {
     *     if (parent::beforeSave($insert)) {
     *         // ...custom code here...
     *         return true;
     *     } else {
     *         return false;
     *     }
     * }
     * ```
     *
     * @param boolean $insert whether this method called while inserting a record.
     * If false, it means the method is called while updating a record.
     * @return boolean whether the insertion or updating should continue.
     * If false, the insertion or updating will be cancelled.
     */
    public function beforeSave($insert)
    {
        if ($this->level_id == UserLevels::LEVEL_DEV || $this->level_id == UserLevels::LEVEL_SUPER_ADMIN) {
            $this->role_id = null;
        }

        if ($this->isNewRecord) {
            $this->generateAuthKey();
        }

        if ($this->isNewRecord || $this->scenario === self::SCENARIO_CHANGE_PASSWORD) {
            $this->setPassword($this->password);
        }

        if ($this->scenario === self::SCENARIO_SIGNUP) {
            $this->generateAccountActivationToken();
        }


        return parent::beforeSave($insert);
    }

    /**
     * This method is called at the end of inserting or updating a record.
     * The default implementation will trigger an [[EVENT_AFTER_INSERT]] event when `$insert` is true,
     * or an [[EVENT_AFTER_UPDATE]] event if `$insert` is false. The event class used is [[AfterSaveEvent]].
     * When overriding this method, make sure you call the parent implementation so that
     * the event is triggered.
     * @param boolean $insert whether this method called while inserting a record.
     * If false, it means the method is called while updating a record.
     * @param array $changedAttributes The old values of attributes that had changed and were saved.
     * You can use this parameter to take action based on the changes made for example send an email
     * when the password had changed or implement audit trail that tracks all the changes.
     * `$changedAttributes` gives you the old attribute values while the active record (`$this`) has
     * already the new, updated values.
     */
    public function afterSave($insert, $changedAttributes)
    {
        $this->updateProfileImage();
        parent::afterSave($insert, $changedAttributes);
    }


    /**
     * Get fetch condition based on the user level
     * @param array $filters
     * @return array
     * @throws ForbiddenHttpException
     */
    public static function getFetchCondition($filters = [])
    {
        $condition = "";
        $params = [];
        foreach ($filters as $k => $v) {
            if (!is_null($v)) {
                if (!empty($condition))
                    $condition .= ' AND ';
                $condition .= "[[{$k}]]=:{$k}";
                $params[':' . $k] = $v;
            }
        }

        switch (Yii::$app->user->identity->level_id) {
            case UserLevels::LEVEL_DEV:
                break;
            case UserLevels::LEVEL_SUPER_ADMIN:
                if (!empty($condition))
                    $condition .= ' AND ';
                $condition .= '([[level_id]] <>"' . UserLevels::LEVEL_DEV . '")';
                break;
            case UserLevels::LEVEL_ADMIN:
                if (!empty($condition))
                    $condition .= ' AND ';
                $condition .= '([[level_id]] <>"' . UserLevels::LEVEL_DEV . '" AND [[level_id]] <>"' . UserLevels::LEVEL_SUPER_ADMIN . '")';
                break;
            default :
                throw new ForbiddenHttpException(Lang::t('403_error'));
        }

        return [$condition, $params];
    }

    /**
     * composes drop-down list data from a model using Html::listData function
     * @see CHtml::listData();
     * @param string $valueColumn
     * @param string $textColumn
     * @param boolean $tip
     * @param array|string $condition
     * @param array $params
     * @param array $options
     * <pre>
     *   array(
     * "orderBy"=>""//String,
     *    "groupField"=>null//String could be anonymous function that gets the group field
     *    "extraColumns"=>null// String you must pass at least the grouping field if groupField is an anonymous function
     * )
     * </pre>
     * @return array
     */
    public static function getListData($valueColumn = 'id', $textColumn = 'name', $tip = true, $condition = ['status' => self::STATUS_ACTIVE], $params = [], $options = [])
    {
        return parent::getListData($valueColumn, $textColumn, $tip, $condition, $params, $options);
    }

    /**
     * Whether the logged in user can update a given user
     * @param string $action
     * @param string $level_id
     * @param boolean $throw_exception
     * @return bool
     * @throws ForbiddenHttpException
     */
    public static function checkPrivilege($action = Acl::ACTION_UPDATE, $level_id, $throw_exception = FALSE)
    {
        $privilege = FALSE;

        if (Yii::$app->user->canUpdate(Constants::RES_USER)) {
            switch (Yii::$app->user->identity->level_id) {
                case UserLevels::LEVEL_DEV:
                    if ($level_id == UserLevels::LEVEL_DEV) {
                        $privilege = ($action === Acl::ACTION_VIEW);
                    } else {
                        $privilege = TRUE;
                    }
                    break;
                case UserLevels::LEVEL_SUPER_ADMIN:
                    if ($level_id == UserLevels::LEVEL_DEV) {
                        $privilege = FALSE;
                    } else if ($level_id == UserLevels::LEVEL_SUPER_ADMIN) {
                        $privilege = ($action === Acl::ACTION_VIEW);
                    } else {
                        $privilege = TRUE;
                    }
                    break;
                case UserLevels::LEVEL_ADMIN:
                    if ($level_id == UserLevels::LEVEL_DEV) {
                        $privilege = FALSE;
                    } else if ($level_id == UserLevels::LEVEL_SUPER_ADMIN || $level_id == UserLevels::LEVEL_ADMIN) {
                        $privilege = ($action === Acl::ACTION_VIEW);
                    } else {
                        $privilege = TRUE;
                    }
                    break;
                default :
                    $privilege = FALSE;
            }
        }
        if (!$privilege && $throw_exception)
            throw new ForbiddenHttpException(Lang::t('403_error'));
        else
            return $privilege;
    }

    /**
     * Check whether account belongs to a user
     * @param string $id
     * @return bool
     */
    public static function isMyAccount($id)
    {
        return ($id == Yii::$app->user->id);
    }


    /**
     * Get user levels to display in the dropdown list
     * @return array
     */
    public static function userLevelOptions()
    {
        $values = UserLevels::getListData('id', 'name', false, '[[id]]<>:t1', [':t1' => UserLevels::LEVEL_ADMIN], ['orderBy' => ['id' => SORT_ASC]]);

        foreach ($values as $k => $v) {
            if (!static::checkPrivilege(Acl::ACTION_VIEW, $k, FALSE)) {
                unset($values[$k]);
            }
        }

        return $values;
    }

    public function sendCreatedUserEmail()
    {
        $template = EmailTemplate::getOneRow('*', ['id' => 'created_user_email']);

        if (empty($template))
            return FALSE;

        /* @var $setting \common\components\Setting */
        $setting = Yii::$app->setting;

        $app_name = $setting->get(ConfConstants::SECTION_SYSTEM, ConfConstants::KEY_APP_NAME, Yii::$app->name);
        //placeholders: {name},{site_name},{link},{username} {email},{password},
        $body = strtr($template['body'], [
            '{{name}}' => $this->name,
            '{{site_name}}' => $app_name,
            '{{link}}' => Yii::$app->getUrlManager()->createAbsoluteUrl(['auth/auth/login']),
            '{{username}}' => $this->username,
            '{{email}}' => $this->email,
            '{{password}}' => $this->password,
        ]);

        $email = [
            'message' => $body,
            'subject' => $template['subject'],
            'sender_name' => $app_name,
            'sender_email' => $template['sender'],
            'recipient_email' => $this->email,
            'type' => EmailQueue::TYPE_CREATED_USER,
            'ref_id' => $this->id,
        ];
        EmailQueue::pushToQueue($email);
    }

    //PROFILE IMAGE HANDLERS

    /**
     * Get the dir of a user
     * @param string $id
     * @return string
     */
    public static function getDir($id)
    {
        return FileManager::createDir(static::getBaseDir() . DIRECTORY_SEPARATOR . $id);
    }

    /**
     * @return mixed
     */
    public static function getBaseDir()
    {
        return FileManager::createDir(FileManager::getUploadsDir() . DIRECTORY_SEPARATOR . self::UPLOADS_DIR);
    }

    /**
     * Update profile image
     */
    protected function updateProfileImage()
    {
        if (empty($this->temp_profile_image))
            return false;
        //using fine-uploader
        $ext = FileManager::getFileExtension($this->temp_profile_image);
        $image_name = Utils::generateSalt() . '.' . $ext;
        $temp_dir = dirname($this->temp_profile_image);
        $new_path = static::getDir($this->id) . DIRECTORY_SEPARATOR . $image_name;
        if (copy($this->temp_profile_image, $new_path)) {
            $this->profile_image = $image_name;
            $this->temp_profile_image = null;
            $this->save(false);

            if (!empty($temp_dir))
                FileManager::deleteDir($temp_dir);

            $this->createThumbs($new_path, $image_name);
        }
    }

    /**
     * Create image thumbs
     * @param string $image_path
     * @param string $image_name
     *
     */
    protected function createThumbs($image_path, $image_name)
    {
        $sizes = [
            ['width' => 32, 'height' => 32],
            ['width' => 64, 'height' => 64],
            ['width' => 128, 'height' => 128],
            ['width' => 256, 'height' => 256],
        ];

        $base_dir = static::getDir($this->id);
        foreach ($sizes as $size) {
            $thumb_name = $size['width'] . '_' . $image_name;
            $new_path = $base_dir . DIRECTORY_SEPARATOR . $thumb_name;
            // generate a thumbnail image
            Image::thumbnail($image_path, $size['width'], $size['height'])
                ->save($new_path, ['quality' => 50]);
        }
    }

    /**
     * Default profile image path
     *
     * @return string
     */
    public static function getDefaultProfileImagePath()
    {
        return Yii::$app->view->theme->getBasePath() . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'default-profile-image.png';
    }

    /**
     * Get profile image
     * @param string $user_id
     * @param string $size
     * @param string $image
     * @return string
     */
    public static function getProfileImageUrl($user_id = null, $size = null, $image = null)
    {
        $image_path = null;
        if (!empty($user_id)) {
            $image_path = NULL;
            $base_dir = static::getDir($user_id);
            if (empty($image)) {
                $image = static::getFieldByPk($user_id, 'profile_image');
            }

            if (!empty($size)) {
                $thumb = $size . '_' . $image;
                $image = file_exists($base_dir . DIRECTORY_SEPARATOR . $thumb) ? $thumb : $image;
            }
            $image_path = $base_dir . DIRECTORY_SEPARATOR . $image;
            //if (!empty($image) && file_exists($image_path))
            //  return Yii::$app->request->getActualBaseUrl() . '/uploads/' . self::UPLOADS_DIR . '/' . $user_id . '/' . $image;
        }


        if (empty($image) || !file_exists($image_path))
            $image_path = static::getDefaultProfileImagePath();

        if (!file_exists($image_path))
            return 'https://placehold.it/150x150';

        $asset = Yii::$app->getAssetManager()->publish($image_path);

        return $asset[1];
    }

    /**
     * Validate user's password
     */
    public function validateCurrentPassword()
    {
        if (!$this->validatePassword($this->currentPassword)) {
            $this->addError('currentPassword', Lang::t('Current password is not valid.'));
        }
    }

    /**
     * Returns true if the currently logged in user is dev
     * @return bool
     */
    public static function isDev()
    {
        return Yii::$app->user->identity->level_id == UserLevels::LEVEL_DEV;
    }

    /**
     * Returns true if the currently logged in user is superadmin/data manager
     * @return bool
     */
    public static function isSuperAdmin()
    {
        return Yii::$app->user->identity->level_id == UserLevels::LEVEL_SUPER_ADMIN;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRole()
    {
        return $this->hasOne(Roles::className(), ['id' => 'role_id']);
    }

    /**
     * @return bool
     */
    public static function isRequirePasswordChange()
    {
        return static::getFieldByPk(Yii::$app->user->id, 'require_password_change');
    }

}
