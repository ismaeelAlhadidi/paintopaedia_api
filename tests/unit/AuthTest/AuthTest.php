<?php 

use PHPUnit\Framework\TestCase;
use App\Authentecation\Auth as RealAuth;
use App\Exceptions\GuardNotFoundException;
use App\Models\User as RealUser;
use \Firebase\JWT\JWT;

class AuthTest extends TestCase {
    
    public static $fake_users = [
        [
            'UserId' => 7253,
            'FName' => 'esmaeel', 
            'LName' => 'al-hadidi',
            'UserName' => 'ismaeel', 
            'PhoneNo' => '0797886161',
            'Email' => 'esmaeel.hadidi@hotmail.com',
            'Password' => '123123123',
            'ProfilePic' => 'text',
            'UserType' => 0,
        ],
    ];

    public static $fake_doctors = [
        [
            'DoctorId' => 0,
            'DoctorIdNo' => '1231231234r12312',
            'Specialization' => 'text',
            'Location'  => 'text',
            'AboutMe' => 'text',
            'ClinicName' => 'text',
            'MedicalSchoolName' => 'text',
        ],
    ];

    public static function setUpBeforeClass() : void {
        $temp = static::$fake_users[0];
        $temp['Password'] = password_hash($temp['Password'], PASSWORD_DEFAULT);
        if(! TestUser::create($temp)) echo "\n ######################################## \n";
    }

    public static function tearDownAfterClass() : void {
        TestUser::where('UserName', static::$fake_users[0]['UserName'])
            ->where('Email', static::$fake_users[0]['Email'])
            ->where('PhoneNo', static::$fake_users[0]['PhoneNo'])
            ->first()->delete();
    }

    /*
    public function test_guard_method() {
        $this->assertTrue (
            method_exists (
                Auth::class, 'guard'
            ),
            'Class ' . Auth::class . ' does not have method guard'
        );

        $auth = Auth::guard('doctor');
        $this->assertInstanceOf(Auth::class, $auth);

        $this->assertEquals($auth->getGuard(), $auth->getGuards()['doctor']);
        

        $this->expectException(\InvalidArgumentException::class);
        $auth = Auth::guard();
    }

    public function test_guard_not_found_exception() {

        $this->expectException(GuardNotFoundException::class);
        $auth = Auth::guard('test');
    }
*/
/*
    public function test_attempt_method() {
        $this->assertTrue (
            method_exists (
                Auth::class, 'attempt'
            ),
            'Class ' . Auth::class . ' does not have method attempt'
        );

        $this->assertFalse(Auth::guard('doctor')->attempt());

        $this->assertFalse(Auth::guard('doctor')->attempt(['email' => '', 'password' => '']));

        $this->assertFalse(Auth::guard('doctor')->attempt(['email' => '', 'password' => '']));

        $this->assertFalse(Auth::guard('doctor')->attempt(['email' => '', 'password' => '']));

        $this->assertFalse(Auth::guard('doctor')->attempt(['email' => '', 'password' => '']));

        $this->assertTrue(Auth::guard('doctor')->attempt(['email' => '', 'password' => '']));

    }
    */

    public function test_attempt_with_default_guard() {

        $this->assertFalse(Auth::attempt("hi"));

        $this->assertFalse(Auth::attempt([]));

        $this->assertFalse(Auth::attempt(['Email' => static::$fake_users[0]['Email']]));

        $this->assertFalse(Auth::attempt(['Password' => static::$fake_users[0]['Password']]));

        $this->assertFalse(Auth::attempt (
            [
                'Email' => static::$fake_users[0]['Email'],
                'Password' => static::$fake_users[0]['Password'] . "1"
            ]
        ));

        $this->assertTrue(Auth::attempt (
            [
                'Email' => static::$fake_users[0]['Email'],
                'Password' => static::$fake_users[0]['Password']
            ]
        ));
    }

    public function test_user_method() {
        $this->assertTrue (
            method_exists (
                Auth::class, 'user'
            ),
            'Class ' . Auth::class . ' does not have method user'
        );
        $user = Auth::user();
        $this->assertInstanceOf(TestUser::class, $user);
        
        $this->assertEquals($user->UserId, static::$fake_users[0]['UserId']);
        $this->assertEquals($user->FName, static::$fake_users[0]['FName']);
        $this->assertEquals($user->LName, static::$fake_users[0]['LName']);
        $this->assertEquals($user->UserName, static::$fake_users[0]['UserName']);
        $this->assertEquals($user->PhoneNo, static::$fake_users[0]['PhoneNo']);
        $this->assertEquals($user->Email, static::$fake_users[0]['Email']);
        $this->assertEquals($user->UserType, static::$fake_users[0]['UserType']);
        $this->assertEquals($user->ProfilePic, static::$fake_users[0]['ProfilePic']);
        
        $this->assertEquals($user->Password, null);
    }

    public function test_id_method() {
        $this->assertTrue (
            method_exists (
                Auth::class, 'id'
            ),
            'Class ' . Auth::class . ' does not have method id'
        );

        $this->assertEquals( Auth::id(), static::$fake_users[0]['UserId']);

        $user = Auth::user();
        $this->assertInstanceOf(TestUser::class, $user);

        $this->assertEquals($user->UserId, Auth::id());

        $this->assertEquals($user->UserId, static::$fake_users[0]['UserId']);
        $this->assertEquals($user->FName, static::$fake_users[0]['FName']);
        $this->assertEquals($user->LName, static::$fake_users[0]['LName']);
        $this->assertEquals($user->UserName, static::$fake_users[0]['UserName']);
        $this->assertEquals($user->PhoneNo, static::$fake_users[0]['PhoneNo']);
        $this->assertEquals($user->Email, static::$fake_users[0]['Email']);
        $this->assertEquals($user->UserType, static::$fake_users[0]['UserType']);
        $this->assertEquals($user->ProfilePic, static::$fake_users[0]['ProfilePic']);

        $this->assertEquals($user->Password, null);
    }

    public function test_logout_method() {

        $this->assertTrue (
            method_exists (
                Auth::class, 'logout'
            ),
            'Class ' . Auth::class . ' does not have method logout'
        );


        $user = Auth::user();
        $this->assertInstanceOf(TestUser::class, $user);
        $this->assertEquals($user->UserId, Auth::id());

        Auth::logout();

        $user = Auth::user();
        $this->assertEquals(null, $user);
        $this->assertEquals(null, Auth::id());

    }

    public function test_check_method() {
        $this->assertTrue (
            method_exists (
                Auth::class, 'check'
            ),
            'Class ' . Auth::class . ' does not have method check'
        );

        $this->assertFalse(Auth::check());
        $user = Auth::user();
        $this->assertEquals(null, $user);
        $this->assertFalse(Auth::check());

        $this->assertTrue(Auth::attempt (
            [
                'Email' => static::$fake_users[0]['Email'],
                'Password' => static::$fake_users[0]['Password']
            ]
        ));
        $user = Auth::user();
        $this->assertInstanceOf(TestUser::class, $user);
        $this->assertEquals($user->UserId, Auth::id());
        $this->assertEquals($user->UserId, static::$fake_users[0]['UserId']);
        $this->assertTrue(Auth::check());

        Auth::logout();

        $this->assertFalse(Auth::check());
        $user = Auth::user();
        $this->assertEquals(null, $user);
        $this->assertFalse(Auth::check());
    }

    public function test_get_token_method() {

        $this->assertTrue(Auth::attempt (
            [
                'Email' => static::$fake_users[0]['Email'],
                'Password' => static::$fake_users[0]['Password']
            ]
        ));

        $user = Auth::user();
        $this->assertInstanceOf(TestUser::class, $user);
        $this->assertEquals($user->UserId, Auth::id());
        $this->assertEquals($user->UserId, static::$fake_users[0]['UserId']);
        $this->assertTrue(Auth::check());

        $token = Auth::get_token();
        $this->assertNotEquals(null, $token);

        $secret_key = Auth::getKey();
        
        try {

            $decoded_token = JWT::decode($token, $secret_key, array('HS256'));

            $decoded_token = ( array ) $decoded_token;

            if(! isset($decoded_token['id'])) $this->fail("error in decode jwt");

            $user_id = $decoded_token['id'];

            $this->assertEquals($user->UserId, $user_id);

            $this->assertEquals($user_id, Auth::id());
        
        } catch (Exception $e) {

            $this->fail($e->getMessage());
        }

        Auth::logout();
        $this->assertEquals(null, Auth::user());
        $this->assertEquals(null, Auth::id());
        $this->assertFalse(Auth::check());
    }


/*
    public function test_login_method() {
        $this->assertTrue (
            method_exists (
                Auth::class, 'login'
            ),
            'Class ' . Auth::class . ' does not have method login'
        );
        // take ( User $user, $remember )
        // return boolean
    }
*/
    /*
    public function test_logoutOtherDevices_method() {
        $this->assertTrue (
            method_exists (
                Auth::class, 'logoutOtherDevices'
            ),
            'Class ' . Auth::class . ' does not have method logoutOtherDevices'
        );
    }
    public function test_once_method() {
        $this->assertTrue (
            method_exists (
                Auth::class, 'once'
            ),
            'Class ' . Auth::class . ' does not have method once'
        );
        // take ( $credentials )
        // return boolean

        // ## authenticate a user with the application for a single request 
    }
    public function test_loginUsingId_method() {
        $this->assertTrue (
            method_exists (
                Auth::class, 'loginUsingId'
            ),
            'Class ' . Auth::class . ' does not have method loginUsingId'
        );
        // take ( $user_id, $remember )
        // return boolean
    }
    */
    
}

class Auth extends RealAuth {

    protected static $default_guard = TestUser::class;
    protected static $handler = 'Email';
    protected static $password = 'Password';

    /*
    public function getGuard() {

        return $this->guard;
    }
    
    public function getGuards() {

        return static::$guards;
    }

    public function getDefaultGuard() {
        
        return $this->default_guard;
    }
    */

    public static function getKey() {

        return static::$key;
    }
}

class TestUser extends RealUser {
    protected static $handler = 'Email';
    protected static $password = 'Password';
}