<?php

class Authentication_model extends CI_Model
{

    /**This method checks if the username field and password field have been filled in
     * and if so check if so checks if they match and entry in the database.  If it does it returns true
     *and if it does not it returns false. It also returns a string that is and is displayed to the user.
     */

    protected $key; // encryption key
    protected $iv; // initialisation vector
    protected $cipher; // encryption mode
    protected $encryptData; // this variable turns encryption on in the database this should ALWAYS be set to
    // true in production

    public function __construct()
    {
        $this->key = '2C2780CF42428724D45E9C63D84D8E63';
        $this->iv = '67E383E9EE06E2220EE1AF307A7AFCF5';
        $this->cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_256, '', MCRYPT_MODE_CBC, '');
        $this->encryptData = false;
    }


    //This method check if username and password matches and return true if they do

    public function authenticateUser($username, $password)
    {
        if ($username == "" || $password == "") //username or password empty
        {
            $response['logged'] = false;
            $response['message'] = 'Please enter username and password' . $username . $password;
        } else // if username and password are complete
        {

            //encrypt username for comparison
            $username = $this->encrypt($username);

            //hash password for comparison
            $salt = $this->generateSalt($username);
            $password = $this->generateHash($salt, $password);

            $response = array();

            $query = $this->db->get_where('user', array('username' => $username, 'password' => $password));


            if ($query->num_rows() > 0) // if authenticated
            {
                $row = $query->row();
                $response['logged'] = true;
                $response['username'] = $this->decrypt($row->username);
                $response['id'] = $row->id;
                session_start();
                $_SESSION['username'] = $this->decrypt($row->username);
                $_SESSION['userId'] = $row->id;

            } else // else if user does not exist
            {
                $response['logged'] = false;
                $response['message'] = 'Invalid Username or Password';

            }
        }

        return $response;

    }

    public function encrypt($string)
    {
        if ($this->encryptData) // if encryption is turned on
        {
            // Encrypt
            if (mcrypt_generic_init($this->cipher, $this->key, $this->iv) != -1) {
                $string = trim($string); // trim string to be encrypted of any whitespace
                $encrypted = mcrypt_generic($this->cipher, $string);
                mcrypt_generic_deinit($this->cipher);
                $encrypted = base64_encode($encrypted);

                return $encrypted;
            }
        } else { //do not encrypt data
            return $string;
        }
    }


    /*
     * This method checks the provided authentication token and if it is valid it authenticates the user
     */

    function generateSalt($username)
    {
        $salt = '$2a$13$'; //specifies bcryt algorithm with 13 rounds
        $salt = $salt . md5(strtolower($username));
        return $salt;
    }


    /*
     * This method generates a random token, appends it to a URI and sends it to the user
     */

    function generateHash($salt, $password)
    {
        $hash = crypt($password, $salt);
        $hash = substr($hash, 29);
        return $hash;
    }


    /*This method is used when the user has forgotten their password to change it
     *to the provided value, the one time authentication field is set to an empty
     * string.
     */

    public function decrypt($encryptedString)
    {
        if ($this->encryptData) //encryption is turned on
        {
            if (mcrypt_generic_init($this->cipher, $this->key, $this->iv) != -1) {
                $encryptedString = base64_decode($encryptedString);
                $decrypted = mdecrypt_generic($this->cipher, $encryptedString);
                mcrypt_generic_deinit($this->cipher);
                $decrypted = rtrim($decrypted); // trim decrypted string of null characters

                return $decrypted;
            }
        } else { // do no decrypt data
            return $encryptedString;
        }


    }

    /**
     *This method checks that the username and password have not already been registered
     * and if not then adds the user details to the database.  It returns a string that is
     * displayed to the user.
     */
    public function insertUserAccount($username, $password, $firstName, $lastName, $emailAddress)
    {
        //encrypt user data
        $username = $this->encrypt($username);
        $firstName = $this->encrypt($firstName);
        $lastName = $this->encrypt($lastName);
        $emailAddress = $this->encrypt($emailAddress);

        // hash password with bcrypt
        $salt = $this->generateSalt($username);
        $password = $this->generateHash($salt, $password); //create hashed salted password


        $accountDetails = array('username' => $username, 'password' => $password,
            'first_name' => $firstName, 'last_name' => $lastName, 'email_address' => $emailAddress);

        $selectUsername = $this->db->get_where('user', array('username' => $username));
        $selectEmailAddress = $this->db->get_where('user', array('email_address' => $emailAddress));

        if ($selectUsername->num_rows() > 0) { // if username already exists in DB
            $response = "Username already exists";
        } elseif ($selectEmailAddress->num_rows() > 0) { // if email address already exists
            $response = "Email address already registered";
        } else { // create user account
            $query = $this->db->insert('user', $accountDetails);
            if ($query) { // if query succeeds
                $response = "Thank you for registering. Please sign in.";
            } else { // if query fails
                $response = "Registration failed.";
            }

        }
        return $response;
    }


    /*
     * returns true if the authenticated user is a member of the supplied group id
     */

    public function oneTimeAuthenticate($token)
    {
        if ($token == "" || $token == "") //token parameter not given or empty string
        {
            return false;

        } else // if username and password fields complete
        {

            $token = $this->encrypt($token);
            $query = $this->db->get_where('user', array('token' => $token));


            if ($query->num_rows() > 0) // if authenticated
            {
                $row = $query->row();
                $username = $row->username;
                $userId = $row->id;
                $_SESSION['username'] = $username;
                $_SESSION['userId'] = $userId;
                return true;

            } else // else if user does not exist
            {
                return false;
            }
        }

    }


    /*
 * returns true if the authenticated user is an admin of the supplied group id
 */

    public function sendResetPasswordEmail($emailAddress)
    {

        //generate random token
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $token = '';

        for ($i = 0; $i < 64; $i++) // generate random string
        {
            $token .= $characters[rand(0, strlen($characters) - 1)];
        }

        //send message
        $message = 'Hi,
            To reset your password please click the following link:
            team-sync.co.uk/authentication/resetpassword?token=' . $token . '
            Many Thanks
            The TeamSync Team';

        $this->email->from('info@team-sync.co.uk', 'Customer Team');
        $this->email->to($emailAddress);
        $this->email->subject('Password Reset');
        $this->email->message($message);
        $this->email->send();
        // echo $this->email->print_debugger();

        //encrypt
        $token = $this->encrypt($token);
        $emailAddress = $this->encrypt($emailAddress);

        //persist to database
        $data = array(
            'token' => $token,
        );

        $this->db->update('user', $data, array('email_address' => $emailAddress));

    }


    /*
   * Generate a unique based on the username
   */

    function changePassword($password)
    {
        $username = $_SESSION['username']; // get username of authenticated user

        $username = $this->encrypt($username); // encrypt username

        $salt = $this->generateSalt($username);
        $password = $this->generateHash($salt, $password);

        $data = array('password' => $password, 'token' => '');

        $this->db->update('user', $data, array('username' => $username));

        if ($this->db->_error_message()) {
            return false;
        } else {
            return true;
        }


    }


    /*
   * Create a hash of the provided password using the bcrypt algorithm
   */

    /**
     * This method return true if the current user is authenticated and false if they are not
     */
    public function isAuthenticated()
    {
        if (isset($_SESSION['username'])) {
            return true;
        } else {
            return false;
        }
    }


    //this method encrypts the string that is passed to it using AES256 encryption

    public function isGroupMember($groupId)
    {
        $query = $this->db->get_where(
            'user_group', array('group_id' => $groupId, 'user_id' => $_SESSION['userId']));

        if ($query->num_rows() > 0) { // if group member
            return true;
        } else {
            return false;
        }

    }

    //this method decrypts the string that is passed to it using AES256 encryption

    public function isGroupAdmin($groupId)
    {


        $this->db->select('is_admin');
        $this->db->from('user_group');
        $this->db->join('role', 'user_group.role_id = role.id');
        $this->db->where('user_group.group_id', $groupId);
        $this->db->where('user_group.user_id', $_SESSION['userId']);

        $query = $this->db->get();

        if ($query->num_rows() > 0) { // group member

            $row = $query->row();
            return $row->is_admin;

        } else {
            return false;
        }

    }


    /*
     * This method returns the users role for the supplied role id, if the user does not have a role for this
     * group it returns false.
     */

    public function getUserRole($groupId)
    {
        $query = $this->db->get_where(
            'user_group', array('user_id' => $_SESSION['userId'], 'group_id' => $groupId));

        if ($query->num_rows() > 0) // if user does no have role
        {
            $row = $query->row();
            $role = $row->role_id;
            return $role;
        } else // return role id
        {
            return false;
        }

    }

    /**
     *This method updates the authenticated user's user account details.  It also checks that the
     * given email is not already in the system.
     */
    public function updateUserAccount($password, $firstName, $lastName, $emailAddress)
    {
        //encrypt user data
        $firstName = $this->encrypt($firstName);
        $lastName = $this->encrypt($lastName);
        $emailAddress = $this->encrypt($emailAddress);

        $selectEmailAddress = $this->db->get_where(
            'user', array('email_address' => $emailAddress, 'id !=' => $_SESSION['userId']));

        if ($selectEmailAddress->num_rows() > 0) { // if email address already exists in DB
            $response = "Email address already exists";
        } elseif ($password == '') { // update account excluding password

            $accountDetails = array('first_name' => $firstName,
                'last_name' => $lastName, 'email_address' => $emailAddress);

            $this->db->where('id', $_SESSION['userId']);
            $query = $this->db->update('user', $accountDetails);

            if ($query) { // if query succeeds
                $response = "User account updated";
            } else { // if query fails
                $response = "Update failed.";
            }

        } else { // update user account including password

            // hash password with bcrypt
            $salt = $this->generateSalt($_SESSION['username']);
            $password = $this->generateHash($salt, $password); //create hashed salted password


            $accountDetails = array('password' => $password,
                'first_name' => $firstName, 'last_name' => $lastName, 'email_address' => $emailAddress);

            $this->db->where('id', $_SESSION['userId']);
            $query = $this->db->update('user', $accountDetails);

            if ($query) { // if query succeeds
                $response = "User account updated";
            } else { // if query fails
                $response = "Update failed.";
            }

        }
        return $response;
    }

}