<?php namespace Gdoo\Calendar\Sabre\Connector;

/**
 * This is an authentication backend that uses a file to manage passwords.
 *
 * The backend file must conform to Apache's htdigest format
 *
 * @package Sabre
 * @subpackage DAV
 * @copyright Copyright (C) 2007-2013 fruux GmbH (https://fruux.com/).
 * @author Evert Pot (http://evertpot.com/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */

class Auth extends \Sabre\DAV\Auth\Backend\AbstractBasic
{
    /**
     * Validates a username and password
     *
     * This method should return true or false depending on if login
     * succeeded.
     *
     * @return bool
     */
    protected function validateUserPass($username, $password)
    {
        if (\Auth::check()) {
            return true;
        } else {
            $credentials = [
                'username' => $username,
                'password' => $password
            ];
            if (\Auth::attempt($credentials)) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Returns information about the currently logged in username.
     *
     * If nobody is currently logged in, this method should return null.
     *
     * @return string|null
     */
    public function getCurrentUser()
    {
        if (\Auth::id() > 0) {
            return \Auth::user()->username;
        }
        return null;
    }
}
