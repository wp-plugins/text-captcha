<?php
/*
Plugin Name: Text Captcha
Plugin URI: http://www.troyvit.com
Description: Text Captcha uses riddles and math to make sure your posters are real instead of computers.
Version: 0.9
Author: Troy Vitullo
Author URI: http://www.troyvit.com
License: GPLv2
*/
$textCaptcha = new text_captcha;

class text_captcha {

	# ------
	# Version info
	# ------
    public $version = '0.1b';

	# ------
	# Variables
	# ------
    public $configFile = 'captcha_config.json'; // it's a json file
    public $errors=array();

	# ----------------------------------------------------------------
	# Constructor
	# ----------------------------------------------------------------
    function text_captcha() {
		add_action('comment_form', array("text_captcha", "build_form"),100);
        add_action('admin_menu', array('text_captcha', 'text_captcha_menu'));
        add_action('admin_head', array('text_captcha', 'text_captcha_settings_toggle'));
        add_filter('preprocess_comment', array('text_captcha', 'text_captcha_verify'), 1);
        add_action( 'init', 'session_start' );
        add_action('wp_ajax_nopriv_text_captcha_validate', array("text_captcha", "text_captcha_validate"));
	}

	# ----------------------------------------------------------------
	# Admin area
	# ----------------------------------------------------------------

    function text_captcha_validate() {
        global $textCaptcha;
		global $user_ID;
		if( $user_ID ) {
            return $id;
        }
		session_start();
		$test_captcha_answer = $_GET['text_captcha_answer'];
		$answer = $_SESSION['answer'];
		// Check if the input matches the answer
		if( $textCaptcha->validate_answer($test_captcha_answer, $answer) ) {
			echo "true";
		} else {
            echo "false";
        }
        die();
        
    }

    function text_captcha_settings_toggle() { ?>
        <script type="text/javascript">
            jQuery().ready(function(){
                jQuery(".toggle").click(function(i) {
                    if(jQuery(this).hasClass('on')) {
                        jQuery(this).removeClass('on');
                        jQuery(this).addClass('off');
                        jQuery(this).val('off');
                        // buttons don't post to forms
                        jQuery("#settings_"+this.id).val('off');
                    } else {
                        jQuery(this).removeClass('off');
                        jQuery(this).addClass('on');
                        jQuery(this).val('on');
                        jQuery("#settings_"+this.id).val('on');
                    }
                });
                jQuery("#addRow").click(function(i) {
                    var max_id=parseInt(jQuery("#max_id").val())+1;
                    var tableRow='<tr><td><input type="text" name="questions['+max_id+'][question]"></td><td> <input type="text" name="questions['+max_id+'][answer]"></td></tr>';
                    jQuery("#max_id").val(max_id);
                    jQuery('#customQuestions > tbody:last').append(tableRow);

                });
                jQuery(".delRow").click(function(i) {
                    var delId=this.id;
                    jQuery("#container"+delId).empty();
                });
            });
        </script>
    <?php
    }

    function text_captcha_menu() {
	    if (is_super_admin()) {
		    add_submenu_page('plugins.php', 'Text CAPTCHA Admin', 'Text CAPTCHA Admin', 'edit_plugins', 'text-captcha', array('text_captcha', 'text_captcha_admin'));
	    }
    }

    function text_captcha_admin() {
        global $textCaptcha;

        $config_dir=dirname(__FILE__);
        $config_file=$config_dir.'/'.$textCaptcha->configFile;

        if(!is_writeable($config_file)) {
            $errors[]='Your config file ('.$config_file.') isn\'t writeable so you can\'t change anything';
            // I don't do anything with this yet
		    // wp_die( __('Your config file isn\'t writeable so you can\'t change anything') );
        }
        
        // see if we need to write to the config file
        if(strlen($_POST['updateForm']) > 0) {
            /* format of toggles:
            $config['settings']['addition']='on';
            $config['settings']['multiplication']='on';
            
            format of questions:
            $config['questions'][0]['question']='What color is the sky on a clear day?';
            $config['questions'][0]['answer']='blue';
             */

            foreach($_POST as $var => $val) {
                // this because I can't get jQuery to play with brackets
                if(strpos($var, 'settings_')===0) {
                    $setting=str_replace('settings_', '', $var);
                    $config['settings'][$setting]=$val;
                }
            }
            // weed out blank questions
            $dirty_config['questions']=$_POST['questions'];
			$i=0;
			foreach($dirty_config['questions'] as $var => $qna) {
			    if(is_array($qna)) {
			        if(strlen($qna['question']) > 0) {
			            $config['questions'][$i]['question']=$qna['question'];
			            $config['questions'][$i]['answer']=$qna['answer'];
			            $i++;
			        }
			    }
			}

            $towrite=json_encode($config);
            $handle = fopen($config_file, 'w');
            if(fwrite($handle, $towrite)===FALSE) {
                echo "failed writing config";
            }
            fclose($handle);
        }

        // Usage of user levels by plugins and themes is deprecated. Use roles and capabilities instead. 
        // roles and capabilities not implemented yet
        // which means that basically anybody who logs onto the admin screen can use this.
        $json=file_get_contents($config_file);
        $config=json_decode($json, TRUE);
        // debug // print_r($config);
		require('text-captcha-admin.php');
    }

	# ----------------------------------------------------------------
	# Captcha form and validation
	# ----------------------------------------------------------------

    function build_form($id) {
        global $user_ID;
        global $textCaptcha;
        if( !$user_ID ) {
            // grab the config
            // select a random question and answer
            // set them in the session
            // display the question in the form
            $question_type=$textCaptcha->select_question();
            $func='tc_'.$question_type;
            $q_and_a=$textCaptcha->$func($config);
            $question=$q_and_a['question'];
            $answer=$q_and_a['answer'];
            $_SESSION['answer']=md5($answer);
            

?>
            <div id="text_captcha">
                <h4>Before you post, please prove you are sentient.</h4> 
                <h4 id="text_captcha_error" style="display:none;">Wait! The answer you gave wasn't the one we were looking for. Try another.</h4>
                <p><?php echo $question; ?> <input id="text_captcha_answer" type="text" name="text_captcha_answer"></p>
            </div>
            <script type="text/javascript">
            <?php 
            if($_POST['text_captcha_error']=='true') { ?>
                document.getElementById("text_captcha_error").style.display='block';
	            commentform = document.getElementById("commentform");
	            commentform.author.value = "<?php echo htmlspecialchars($_POST['author1']); ?>";
	            commentform.email.value = "<?php echo htmlspecialchars($_POST['email1']); ?>";
	            commentform.url.value = "<?php echo htmlspecialchars($_POST['url1']); ?>";
	            commentform.comment.value = "<?php $trans = array("\r" => '\r', "\n" => '\n');
	            echo strtr(htmlspecialchars($_POST['comment1']), $trans); ?>";
            <?php } ?>

            var text_captcha_form=document.getElementById('text_captcha');
            // var p2 = document.getElementsByClassName('form-submit')[0];
            var p2 = document.getElementById('submit');
            p2.parentNode.insertBefore(text_captcha_form,p2);
            </script>

        <?php
        }
    }

    function text_captcha_verify($id) {
        global $textCaptcha;
		global $user_ID;
		if( $user_ID ) {
            return $id;
        }
		session_start();
		$test_captcha_answer = $_POST['text_captcha_answer'];
		$answer = $_SESSION['answer'];
		
		// Check if the input matches the answer
		if( $textCaptcha->validate_answer($test_captcha_answer, $answer) ) {
			return $id;
		}
        // handle this more gracefully
		wp_die('<strong>Text Captcha</strong>: captcha error.');
    }

	# ----------------------------------------------------------------
	# Helper function(s)
	# ----------------------------------------------------------------

    function validate_answer ( $test, $answer ) {
        $encoded_test=strtolower(md5(trim($test)));

		if( $encoded_test == strtolower(trim($answer)) ) {
			return true;
		}
		return false;
    }

    function load_config() {
        global $textCaptcha;
        $config_dir=dirname(__FILE__);
        $config_file=$config_dir.'/'.$textCaptcha->configFile;
        $json=file_get_contents($config_file); 
        $config=json_decode($json, TRUE);
        return $config;
    }

    function select_question() {
        global $textCaptcha;
        // first find out who the contenders are
        $config=$textCaptcha->load_config();
        foreach($config['settings'] as $var => $val) {
            if($val=='on') {
                $list[]=$var;
            }
        }
        $choice=array_rand($list);
        return $list[$choice];
    }

    function tc_multiplication() {
        global $textCaptcha;
        $operator_phrase=array('times', 'multiplied by', '*');
        $key=array_rand($operator_phrase);
        $operator=$operator_phrase[$key];
        $nums = $textCaptcha-> return_numbers();
        $ret['question']='What is '.$nums[0].' '.$operator.' '.$nums[1].'?';
        $ret['answer']=$nums[0]*$nums[1];
        return $ret;
    }

    function tc_addition($config) {
        global $textCaptcha;
        $operator_phrase=array('plus', 'in addition to', '+');
        $key=array_rand($operator_phrase);
        $operator=$operator_phrase[$key];
        $nums = $textCaptcha-> return_numbers();
        $ret['question']='what is '.$nums[0].' '.$operator.' '.$nums[1].'?';
        $ret['answer']=$nums[0]+$nums[1];
        return $ret;
    }

    function return_numbers() {
        $num[]=rand(2,9);
        $num[]=rand(2,9);
        return $num;
    }

    function return_verbal($number_arr) {
        // make your hash
        $number_hash=array(
            1=>'one',
            2=>'two',
            3=>'three',
            4=>'four',
            5=>'five',
            6=>'six',
            7=>'seven',
            8=>'eight',
            9=>'nine'
        );
        foreach($number_arr as $var => $val) {
            $ret[$var]=$number_hash[$val];
        }
        return $ret;
    }

    function tc_questions($config) {
        global $textCaptcha;
        $config=$textCaptcha->load_config();
        $questions=$config['questions'];
        $key = array_rand($questions);
        $ret = $questions[$key];
        return $ret;
    }
}
?>
