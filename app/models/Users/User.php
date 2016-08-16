<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;
use lib\date\Date;

use modules\gamification\traits\UserGamificationTrait;
use modules\institutions\models\Institution;
use modules\collaborative\models\Comment;
use modules\collaborative\models\Like;

class User extends Eloquent implements UserInterface, RemindableInterface {

	use UserTrait, RemindableTrait;
	
	use UserGamificationTrait;

	protected $fillable = ['id','name','email','password','login','verify_code'];

	protected $date;

	public function __construct($attributes = array(), Date $date = null) {
		parent::__construct($attributes);
		$this->date = $date ?: new Date;
	}

	public function news()
    {
        return $this->hasMany('modules\news\models\News');
    }

	public function notifications()
 	{
    	return $this->hasMany('\Tricki\Notification\Models\NotificationUser');
  	}

	public function photos()
	{
		return $this->hasMany('Photo')->whereNull('institution_id');
	}
	public function userPhotos($user_id){
		return $this->hasMany('Photo')->where('user_id', $user_id)->whereNull('institution_id');
	}
	public function comments()
	{
		return $this->hasMany('modules\collaborative\models\Comment');
	}
	public function evaluations()
	{
		return $this->hasMany('modules\evaluations\models\Evaluation');
	}

	public function likes()
	{
		return $this->hasMany('modules\collaborative\models\Like');
	}
	
	public function albums()
	{
		return $this->hasMany('Album');
	}
	public function userAlbums()
	{
		return $this->hasMany('Album')->whereNull('institution_id');
	}
	public function occupation()
	{
		return $this->hasOne('Occupation');
	}

	//seguidores
	public function followers()
	{
		return $this->belongsToMany('User', 'friendship', 'followed_id', 'following_id');
	}

	//seguindo
	public function following()
	{
		return $this->belongsToMany('User', 'friendship', 'following_id', 'followed_id');
	}

	public function institutions(){
		return $this->belongsToMany('modules\institutions\models\Institution', 'friendship_institution','institution_id', 'following_user_id');
	}

	public function followingInstitution(){
		return $this->belongsToMany('modules\institutions\models\Institution', 'friendship_institution','following_user_id', 'institution_id');
	}

	public function roles()
	{
		return $this->belongsToMany('Role', 'users_roles');
	}

	protected $hidden = array('password', 'remember_token');

	public static function createUser($name,$email,$password,$login,$verification)
	{ //create user with a verify code      
      $user = User::create([
      'name' => $name,
      'email' => $email,
      'password' => $password,
      'login' => $login,
      'verify_code' => $verification       
      ]);
      return $user;
	}

	public static function checkOldAccount( $user, $password)
	{
		$verify = exec('java -cp "' . public_path() . '/java:' . public_path() . '/java/jasypt-1.7.jar" PasswordValidator ' . $password . ' ' . $user->password);
		if ( strcmp($verify, 'true') == 0 ) return true;
		return false;
	}

	public static function stoa($stoa_user) {

		$user = User::where('login', 'stoa_' . $stoa_user->nusp)->first();

		if (!$user) {
			$user = User::newStoaUser($stoa_user);
		}

		if ($stoa_user->image_base64) {
			User::saveProfileImage($user, $stoa_user->image_base64);
		}

		return $user;
	}

	private static function newStoaUser($stoa_user) {
		$user = new User();
		$user->name = $stoa_user->first_name;
		$user->email = $stoa_user->email;
		$user->password = 'stoa';
		$user->login = 'stoa_' . $stoa_user->nusp;
		$user->id_stoa = 'stoa_' . $stoa_user->nusp;
		if ($stoa_user->surname)
			$user->name = $user->name . ' ' . $stoa_user->surname;
		if ($stoa_user->homepage)
			$user->site = $stoa_user->homepage;
		$user->save();

		return $user;
	}

	private static function saveProfileImage($user, $image) {
		$user->photo = "/arquigrafia-avatars/".$user->id.".jpg";
		$user->save();
		$image = Image::make(base64_decode($image))->encode('jpg', 80);
		$image->save(public_path().'/arquigrafia-avatars/'.$user->id.'.jpg');
		$image->save(public_path().'/arquigrafia-avatars/'. $user->id."_original.jpg");
	}

	public function equal($user) {
		try {
			return $user instanceof User &&
				$this->id == $user->id;
		} catch (Exception $e) {
			return false;
		}
	}

	public static function userInformation($login)
	{	
		$user = User::whereRaw('((login = ?) or (email = ?)) and (id_stoa is NULL or id_stoa != login) and (id_facebook is NULL or id_facebook != login)', array($login, $login))->first();
		//$testuser = User::whereRaw('((login = ?) or (email = ?)) and (id_stoa is NULL or id_stoa != login)', array($login, $login))->first();
        return $user;
	}
	

	public static function userInformationObtain($email){
		$user = User::where('email','=',$email)->whereRaw('(id_stoa is NULL or id_stoa != login) and
		 (id_facebook is NULL or id_facebook != login)')->first();
		//$testuser = User::where('email','=',$email)->whereRaw('(id_stoa is NULL or id_stoa != login)')->first();
          return $user;
	}

	public static function userAccountsByEmail($email){
		$user = User::where('email','=',$email)->get();
		return $user;
	}

	public static function userAccountInFacebook($email){
		//$fb_acc = User::whereRaw('(email = ?) and (id_facebook = login)', array($email))->first();  
		$user = User::whereRaw('(email = ?) and (id_facebook = login)', array($email))->first();
		return $user;
	}

	public static function userAccountInStoa($email){
		//$stoa_acc = User::whereRaw('(email = ?) and (id_stoa = login)', array($email))->first();
		$user = User::whereRaw('(email = ?) and (id_stoa = login)', array($email))->first();
		return $user;
	}

	public static function updateAccountFacebook($arqAccount,$faceAccount){		
		DB::table('users')->where('id', '=', $arqAccount->id)->update(array('id_facebook' => $faceAccount->id));
	}

	public static function oldUserWhitFacebookIdOrLogin($fbid){
		$user = User::where('id_facebook', '=', $fbid)
		->orWhere('login', '=', $fbid)->first();
		return $user;
	}

	public static function borrar($email){ 
		$arq_acc =  User::whereRaw('(email = ?) and (id_stoa is NULL or id_stoa != login) and 
		(id_facebook is NULL or id_facebook != login)', array($email))->first();
		return $arq_acc;
	}


	public static function userVerifyCode($verify_code){
		$newUser = User::where('verify_code','=',$verify_code)->first();			
        return $newUser;
	}

	public static function userBelongInstitution($login,$institution)
	{
		Log::info("Begining userBelongInstitution with input params login=".$login.", institution=".$institution);
		$employees = DB::table('employees')
    			->join('users','employees.user_id','=','users.id')
    			->join('institutions','employees.institution_id','=','institutions.id')
    			->select('institutions.id')
     			->where('employees.institution_id', $institution)
     			->where('users.login',$login)
     			->orWhere('users.email',$login)
     			->get();
     			
     			if (!empty($employees)){
     				return true;
     			}else{
     				return false;
     			}
    }

	public function setBirthdayAttribute($birthday) {
		$this->attributes['birthday'] = $this->date->formatDate($birthday);
	}

	public function updateAccount($password) {
		$this->oldAccount = 0;
		$this->password = Hash::make($password);
		$this->save();
	}

	public static function updateUserToFriendship($accountFrom, $accountTo)
	{
      	DB::table('friendship')->where('following_id', '=', $accountFrom->id)->update(array('following_id' => $accountTo->id));
    	DB::table('friendship')->where('followed_id', '=', $accountFrom->id)->update(array('followed_id' => $accountTo->id));      
  	} 

  	public static function DeleteParallelAccountsFacebook($fb_acc)
  	{	
  		User::where('id', '=', $fb_acc->id)->delete();
  	}

  	public static function DeleteParallelAccountsStoa($stoa_acc)
  	{	
  		User::where('id', '=', $stoa_acc->id)->delete();
  	}

  	public static function userPhotosSearch($username)
  	{ 
  		$query = User::where('id', '>', 0);
        $query->where('name', 'LIKE', '%'. $username .'%');
        $userList = $query->get();
        return $userList->lists('id');
  	}

}