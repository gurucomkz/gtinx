<?
// controller for user functions
Class CoreUserPartController {
       
	   function __construct() 
		{		
			global $DB;
		//new switch for GET	

//			switch($_POST['act'])
//			{
				/*default:
					$this->ListGroup();
					break;*/
//			}
			
			switch($_GET['act'])
			{
				//case 'setpagecnt': echo "foool"; print_r($GLOBALS); die;
				case 'logout': $this->TryLogout(); break;
				case 'gfx': GtinxCaptchaDraw($_GET['random_num']); break;
			}
			
        }
		
		function TryLogout(){
			global $APP;
			$APP->LogoutOne();
			header("Location: /");
			die();
		}
					/*END TYPES*/
		function __destruct()
		{
		}
		
}
?>