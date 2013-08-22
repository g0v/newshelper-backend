<?php

class LoginController extends Pix_Controller
{
    protected function getGoogleConsumer()
    {
        set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/../stdlibs/php-openid');
        include(__DIR__ . '/../stdlibs/php-openid/Auth/OpenID/Consumer.php');
        include(__DIR__ . '/../stdlibs/php-openid/Auth/OpenID/AX.php');
        include(__DIR__ . '/../stdlibs/php-openid/Auth/OpenID/PAPE.php');
        include(__DIR__ . '/../stdlibs/php-openid/Auth/OpenID/Interface.php');

        $store = new AuthOpenIDSessionStore();
        $consumer = new Auth_OpenID_Consumer($store);
        return $consumer;
    }

    public function googledoneAction()
    {
        $consumer = $this->getGoogleConsumer();
        $return_to = 'http://' . $_SERVER['HTTP_HOST'] . '/login/googledone';
        $response = $consumer->complete($return_to);

        if ($response->status == Auth_OpenID_CANCEL) {
            return $this->alert('取消登入', '/');
        }
        if ($response->status == Auth_OpenID_FAILURE) {
            return $this->alert('登入失敗: ' . $response->message, '/');
        }

        $ax = new Auth_OpenID_AX_FetchResponse();
        $obj = $ax->fromSuccessResponse($response);
        $email = $obj->data['http://axschema.org/contact/email'][0];

        if (!$user = User::search(array('user_name' => 'google://' . $email))->first()) {
            $user = User::insert(array('user_name' => 'google://' . $email));
        }
        Pix_Session::set('user_id', $user->user_id);
        return $this->redirect('/');
    }

    public function googleAction()
    {
        $consumer = $this->getGoogleConsumer();
        $url = 'https://www.google.com/accounts/o8/id';
        $auth_request = $consumer->begin($url);

        if (!$auth_request) {
            return $this->alert('Authentication error, not a valid OpenID', '/');
        }

        $ax = new Auth_OpenID_AX_FetchRequest;
        $ax->add(Auth_OpenID_AX_AttrInfo::make('http://axschema.org/contact/email',2,1, 'email'));
        $auth_request->addExtension($ax);

        $pape_request = new Auth_OpenID_PAPE_Request(null);
        $auth_request->addExtension($pape_request);

        $form_id = 'openid_message';
        $form_html = $auth_request->htmlMarkup('http://' . $_SERVER['HTTP_HOST'], 'http://' . $_SERVER['HTTP_HOST'] . '/login/googledone',
                                               false, array('id' => $form_id));

        // Display an error if the form markup couldn't be generated;
        // otherwise, render the HTML.
        if (Auth_OpenID::isFailure($form_html)) {
            $this->alert("Could not redirect to server: " . $form_html->message, '/');
        } else {
            print $form_html;
            return $this->noview();
        }
    }

    public function logoutAction()
    {
        Pix_Session::delete('user_id');
        return $this->redirect('/');
    }
}
