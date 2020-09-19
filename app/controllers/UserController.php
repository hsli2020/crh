<?php

namespace App\Controllers;

use App\Models\Users;
use App\Models\UserProjects;

class UserController extends ControllerBase
{
    public function indexAction()
    {
    }

    private function _registerSession($user)
    {
        $this->session->set('auth', array(
            'id'       => $user->id,
            'username' => $user->username,
            'role'     => $user->role
        ));
    }

    public function loginAction()
    {
        $this->view->pageTitle = 'User Login';

        $auth = $this->session->get('auth');
        if (is_array($auth)) {
            return $this->response->redirect("/");
        }

        $username = '';

        if ($this->request->isPost() && $this->security->checkToken()) {
            $state = '';

            // Receiving the variables sent by POST
            $username = $this->request->getPost('username', 'trim');
            $password = $this->request->getPost('password', 'trim');

            // find user in the database
            $user = Users::findFirstByUsername($username);

            if ($user && $user->active == 'Y' && $this->security->checkHash($password, $user->password)) {
                $this->_registerSession($user);
                $this->flashSession->success("Welcome, $username!");
                $this->logUserLogin($username, 'Success', $this->request);
                return $this->response->redirect("/");
            } else {
                $this->logUserLogin($username, 'Failed', $this->request);
                $this->flashSession->error('Wrong Username/password.');
                // To protect against timing attacks. Regardless of whether a user exists or not,
                // the script will take roughly the same amount as it will always be computing a hash.
                $this->security->hash(rand());
            }

            //$this->getFlashSession('error', 'Wrong email/password.', false);
        }

        $this->view->username = $username;
    }

    protected function logUserLogin($username, $state, $request)
    {
        $this->db->insertAsDict('user_login', [
            'username' => $username,
            'status'   => $state,
            'ip'       => $request->getClientAddress(),
            'ua'       => $request->getUserAgent(),
        ]);
    }

    public function logoutAction()
    {
        $this->session->destroy();
        return $this->response->redirect("/user/login");
    }

    public function addAction()
    {
        $this->view->pageTitle = 'Add New User';

        // user has to be logged-in to operate
        $auth = $this->session->get('auth');
        if (!is_array($auth)) {
            return $this->response->redirect("/user/login");
        }

        // user has to be admin to operate
        if ($auth['role'] != 1) {
            return $this->response->redirect("/");
        }

        if ($this->request->isPost() && $this->security->checkToken()) {
            $username = $this->request->getPost('username', 'trim');
            $password = $this->request->getPost('password', 'trim');
            $email    = $this->request->getPost('email',    'trim');

            try {
                $user = new Users();
                $user->username = $username;
                $user->email    = $email;
                $user->role     = 0; // TODO ??
                $user->active   = 'Y';
                $user->password = $this->security->hash($password);
                $user->save();

                $userProjects = new UserProjects();
                $userProjects->userId = $user->id;
                $userProjects->projects = '*'; // all projects by default
                $userProjects->save();

                $this->flashSession->success("The user '$username' added successfully.");
            } catch (\Exception $e) {
               #fpr($e->getMessage());
               #$this->flashSession->error($e->getMessage());
                $this->flashSession->error("Failed to add user '$username'");
                return;
            }

            //return $this->response->redirect("/");
        }
    }

    public function changePasswordAction()
    {
        $this->view->pageTitle = 'Change Password';

        // user has to be logged-in to operate
        $auth = $this->session->get('auth');
        if (!is_array($auth)) {
            return $this->response->redirect("/user/login");
        }

        if ($this->request->isPost() && $this->security->checkToken()) {
            $oldPassword  = $this->request->getPost('password_old');
            $newPassword  = $this->request->getPost('password_new');
            $retypePasswd = $this->request->getPost('password_new_retype');

            if ($newPassword != $retypePasswd) {
                return; // retry
            }

            $username = $auth['username'];

            $user = Users::findFirstByUsername($username);

            if ($user && $this->security->checkHash($oldPassword, $user->password)) {
                try {
                    $user->password = $this->security->hash($newPassword);
                    $user->save();
                    $this->flashSession->success("Your password changed successfully.");
                } catch (\Exception $e) {
                    //fpr($e->getMessage());
                    return; // retry
                }

                return $this->response->redirect("/");
            }
        }
    }

    public function resetPasswordAction()
    {
        echo __METHOD__;
    }

    public function forgotPasswordAction()
    {
        echo __METHOD__;
    }

    public function seedAction()
    {
        return;

        // user has to be logged-in to operate
        $auth = $this->session->get('auth');
        if (!is_array($auth)) {
            return $this->response->redirect("/user/login");
        }

        // user has to be admin to operate
        if ($auth['role'] != 1) {
            return $this->response->redirect("/");
        }

        // the passwords got from http://passwordsgenerator.net/
        $passwords = [
            1 => 'gcshs12345',
            2 => 'gcsws12345',
            3 => 'bp8V4FSJdU',
            4 => 'aegb4Sy2Ad',
        ];

        foreach ($passwords as $id => $password) {
            $user = Users::findFirst($id);
            $user->password = $this->security->hash($password);
            $user->save();
        }

        echo "<h2>users seeding is done.</h2>";
    }
}
