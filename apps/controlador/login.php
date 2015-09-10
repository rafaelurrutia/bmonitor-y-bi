<?php
class login extends Control
{

    public function index()
    {

        $this->plantilla->load("login/login",66000);
        
        $section = new Section();

        $tps_index_control['menu'] = $section->menu('Login',true);
        $tps_index_control['header'] = $section->header();
        $tps_index_control['footer'] = $section->footer();
        
        $this->plantilla->set($tps_index_control);
        $this->plantilla->finalize();
        
    }

    public function select()
    {
        $this->plantilla->load("login/select", 66000);
        
        $section = new Section();

        $tps_index_control['menu'] = $section->menu('Login',true);
        $tps_index_control['header'] = $section->header();
        $tps_index_control['footer'] = $section->footer();
        
        $tps_index_control['USER_LOGIN'] = $this->language->USER_LOGIN;
        $tps_index_control['USER_PASS'] = $this->language->USER_PASS;
        $tps_index_control['USER_LANG'] = $this->language->USER_LANG;
        $tps_index_control['USER_BUTTON'] = $this->language->USER_BUTTON;

        
        $this->plantilla->set($tps_index_control);
        $this->plantilla->finalize();  
    }
    
    public function perfil()
    {
        $this->plantilla->load("login/login_perfil");
        $section = new Section();

        $tps_index_control['menu'] = $section->menu('Profile');
        $tps_index_control['header'] = $section->header();
        $tps_index_control['footer'] = $section->footer(false,true,false);
        
        $tps_index_control['PROFILE'] = $this->language->PROFILE;

        $this->plantilla->set($tps_index_control);
        $this->plantilla->finalize();
    }

    public function getPerfil()
    {
        $this->plantilla->load("login/login_form");

        if(isset($_SESSION['iduser']) && $_SESSION['iduser'] > 0) {
            $get_user_sql = sprintf("SELECT `name`,`lifetime`,`lang`,`email`,`theme` FROM %s WHERE `id_user` = '%s' AND active = 'true'", $this->protect->table_user, $_SESSION['iduser']);
            $user = $this->conexion->queryFetch($get_user_sql);
            if($user) {

                $tps_index_control['user_name'] = $user[0]['name'];
                $tps_index_control['user_email'] = $user[0]['email'];
            }
        }
        
        $tps_index_control['GENERAL'] = $this->language->GENERAL;
        $tps_index_control['NAME'] = $this->language->NAME;
        $tps_index_control['SAVE'] = $this->language->SAVE;
        $tps_index_control['CHANGE_PASSWORD'] = $this->language->CHANGE_PASSWORD;
        $tps_index_control['FORM_ALL_PARAM_REQUIRED'] = $this->language->FORM_ALL_PARAM_REQUIRED;
        $tps_index_control['CURRENT_PASSWORD'] = $this->language->CURRENT_PASSWORD;
        $tps_index_control['NEW_PASSWORD'] = $this->language->NEW_PASSWORD;
        $tps_index_control['CONFIRMATION'] = $this->language->CONFIRMATION;
        $tps_index_control['TEMPLATE_LANG'] = $this->language->TEMPLATE_LANG;

        $this->plantilla->set($tps_index_control);
        $this->plantilla->finalize();
    }

    public function access()
    {
        $post = (object)$_POST;

        if ($post->lang == 'false') {
            $lang = false;
        } else {
            $lang = $post->lang;
        }

        if (!isset($post->empresas)) {
            $post->empresas = 0;
        }

        $login = $this->protect->login($post->username, $post->password, $post->empresas, $lang);
        $this->basic->jsonEncode($login);
        exit;
    }

    public function logout()
    {
        if (isset($_SESSION['iduser'])) {
            $result['msg'] = '';
            $result['status'] = true;
            $result['redirect'] = 'http://' . URL_BASE_FULL . "login";
            $this->protect->log_out();
            header("Content-type: application/json");
            echo json_encode($result);
        } else {
            $result['msg'] = "Session no activa";
            $result['status'] = false;
            header("Content-type: application/json");
            echo json_encode($result);
        }
    }

    public function new_pass()
    {
        $callback = $_GET['callback'];

        $respond = $this->protect->new_pass($_POST['password_active'], $_POST['password']);

        if ($respond['error'] === 0) {
            $respond['msg'] = $this->language->NEW_PASS_OK;
        } elseif ($respond['error'] === 1) {
            $respond['msg'] = $this->language->NEW_PASS_ERROR_1;
        } elseif ($respond['error'] === 2) {
            $respond['msg'] = $this->language->NEW_PASS_ERROR_2;
        } elseif ($respond['error'] === 3) {
            $respond['msg'] = $this->language->NEW_PASS_ERROR_3;
        }
		
        //echo $callback . '(/* API Bmonitor */' . json_encode($respond) . ')';  
        echo json_encode($respond);
    }
}
?>