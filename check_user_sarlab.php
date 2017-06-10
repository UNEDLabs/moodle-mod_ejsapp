<?php
// This file is part of the Moodle module "EJSApp"
//
// EJSApp is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// EJSApp is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.
//
// EJSApp has been developed by:
// - Luis de la Torre: ldelatorre@dia.uned.es
// - Ruben Heradio: rheradio@issi.uned.es
//
// at the Computer Science and Automatic Control, Spanish Open University
// (UNED), Madrid, Spain.

/**
 * Authentication and privileges verification between Moodle and Sarlab for the experiences manager.
 *
 * Receives an encrypted username and password from Sarlab and checks whether that user exists in Moodle
 * and has, at least, a teacher role in, at least, one course.
 *
 * @package    mod_ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

global $DB, $CFG;

// Receive encrypted username and password.
$username = $_GET["username"];
$password = $_GET["password"];

// Function rawurldecode must be used due to problems with symbols like + or = when obtained via GET.
$username = rawurldecode($username);
$password = rawurldecode($password);

// Decrypt username and password.
$encryption = new MCrypt();
$username = $encryption->decrypt($username);
$password = $encryption->decrypt($password);

$listsarlabips = explode(";", get_config('block_remlab_manager', 'sarlab_IP'));

foreach ($listsarlabips as $sarlabip) {

    $ip = substr($sarlabip, strrpos($sarlabip, "'") + 1);

    if ($_SERVER['REMOTE_ADDR'] == $ip) { // Allow connections only from a registered Sarlab server.

        if ( isset($username) && !empty($username) && isset($password) && !empty($password) ) {

            $user = authenticate_user_login($username, $password);

            if ($user != false) { // Only users registered in Moodle are allowed.
                if ($user->id == 2) { // If admin user.
                    echo "access=true\n";
                    // echo '{"access":"true"}';
                } else {
                    $access = false;
                    $roles = $DB->get_records('role_assignments', array('userid' => $user->id));
                    foreach ($roles as $role) {
                        if ($role->roleid <= 3) { // User is teacher in, at least, one course.
                            echo "access=true\n";
                            // echo '{"access":"true"}';
                            $access = true;
                            break;
                        }
                    }
                    if (!$access) {
                        echo "access=false\n";
                        // echo '{"access":"false"}';
                    } // Otherwise, we don't let him in.
                }
            } else {
                echo "access=false\n";
                // echo '{"access":"false"}';
            }

        }

        break;

    }

}

/**
 * Class for encryption
 *
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MCrypt {

    /** @var string It doesn't affect to the ECB encryption method */
    private $iv = '0000000000000000';
    /** @var string  Same as in the JAVA code on the corresponding Sarlab server */
    private $key;

    /**
     * Constructor
     *
     */
    public function __construct() {
        // Retrieve the key from the configuration of the ejsapp plugin.
        $this->key = get_config('block_remlab_manager', 'sarlab_enc_key');
        if ($this->key == null) {
            echo "WARNING: The encryption key has not been configured in the EJSApp plugin. Edit the settings of the
             EJSApp plugin to fix this.";
        } else if (strlen($this->key) != 16) {
            echo "WARNING: An encryption key has been found but it does not have the required number of characters!
             Edit the settings of the EJSApp plugin to fix this.";
        }
    }

    /**
     * Encrypts a string
     *
     * @param string $str
     * @return string
     */
    public function encrypt($str) {
        $iv = $this->iv;

        $td = mcrypt_module_open('rijndael-128', '', 'ecb', '');

        mcrypt_generic_init($td, $this->key, $iv);
        $blocksize = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $padding = $blocksize - (strlen($str) % $blocksize);
        $str .= str_repeat(chr($padding), $padding);
        $encrypted = mcrypt_generic($td, $str);

        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return base64_encode($encrypted);
    }

    /**
     * Decrypts a string
     *
     * @param string $code
     * @return string
     */
    public function decrypt($code) {
        $code = base64_decode($code);

        $iv = $this->iv;

        $td = mcrypt_module_open('rijndael-128', '', 'ecb', '');

        mcrypt_generic_init($td, $this->key, $iv);
        if (function_exists('mdecrypt_generic')) {
            $decrypted = mdecrypt_generic($td, $code);
        } else {
            echo "mcrypt not installed in your system";
        } // Can happen in unix systems?

        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return utf8_encode(trim($decrypted));
    }

}