<?php
if(!defined('PROJECT_NAME')) die('project empty');
class adminControl extends sysControl{
	
	public function admin_list(){
		$selected = selected(array('user_type'));
		$Madmin = M('admin');
		$is_del = array('is_del' => '0');
		$Madmin->where($is_del);
		$page= isset($_POST['page']) ? intval($_POST['page']) : (isset($_GET['page']) ? intval($_GET['page']) : 1);
		$num = 10;  	//显示的数量
		$where = search('mobile|username');
		if($selected['user_type']){
			$user_type = explode('/',$selected['user_type']);
			if(isset($user_type[1]) && !empty($user_type[1])){
				$Madmin->where(array('user_type' => $user_type[1]));
			}
		}
		if(!empty($where)){
			$Madmin->where($where);
		}
		$admin = $Madmin->page($page,$num)->select();
		$count = $Madmin->count();
		
		$page_obj = new page($count,$num,$page,'javascript:;',5);
		$page_obj ->page_attr();
		$page_obj ->conf = 23456;
		self::output('page',$page_obj ->show());
	//	$_POST['keyword'] = isset($_POST['keyword']) ? $_POST['keyword'] : '';
		self::form_top(array(
			'selected' => array(	//下拉选择
				$selected['user_type'] => array( 	//字段二
					'0' => '请选择',
					'1' => '超级管理员',
					'2' => '普通管理员',
				),
			),
			'keyword' => I('keyword'),
			'all_del' => 'url',	//批量删除
			'add' => '?act=admin&op=admin_add',
			'search',
			'export' => 'url',
		));
		
		self::form_list(array(
			array('checkbox','id','ID',array('style'=>'max-width:300px;max-height:300px;')),
			array('label','username','用户名'),
			array('label','password','密码'),
			array('radio','user_type','管理员类型',array('1' => '超级管理员' , '2' => '普通管理员')),
			array('time','add_time','时间'),
			array('menu',array(
					array('编辑','javascript:;',array('style'=>'background:#FF5722','onclick' => "question_edit('编辑','?act=admin&op=admin_edit&id=__ID__','','','')")),
					array('删除','javascript:;',array('onclick' => "question_del(this,'?act=admin&op=admin_del&id=__ID__')")),
					),'操作'),
		),$admin,'id');
	}
	
	public function admin_add(){
		if($_POST){
			if(strlen($_POST['password']) < 6 || strlen($_POST['password']) > 30){
				show_message(array( 'msg' =>'长度请设置6-30位' , 'code' => '-1'),'json');
			}
			
			$this->commit();
		}
		self::form("this",array(
			array('hidden','id','ID'),
			array('text','username','用户名'),
			array('password','password','密码'),
			array('password','password2','重复密码'),
			array('text','email','邮箱'),
			array('text','mobile','手机号'),
			array('radio','state','单选',array('启用','禁止')),
			array('selected','user_type','下拉',array('1' => '超级管理员', '2' => '普通管理员')),
		),'','post','public_form');
	}
	
	public function admin_edit(){
		if($_POST){
			$this->commit();
		}
		$admin = '';
		if(isset($_GET['id']) && $_GET['id'] > 0){
			$admin = M('admin')->where(array('id' => intval($_GET['id'])))->find();
		}
		self::form("this",array(
			array('hidden','id','ID'),
			array('text','username','用户名',array('disabled' => 'disabled')),
			array('password','password','密码'),
			array('password','password2','重复密码'),
			array('text','email','邮箱'),
			array('text','mobile','手机号'),
			array('radio','state','单选',array('启用','禁止')),
			array('selected','user_type','下拉',array('1' => '超级管理员', '2' => '普通管理员')),
		),$admin,'post','public_form');
	}
	
	public function admin_del(){
		$id = intval($_GET['id']);
		if($id){
			$res = M('admin')->where(array('id' => $id))->update(array('is_del' => '1'));
			if($res){
				show_message(array('code' => '1' , 'msg' => '删除成功'),'json');
			}else{
				show_message(array('code' => '-1' , 'msg' => '删除失败'),'json');
			}
		}
	}
	
	private function commit(){
		if(!empty($_POST['password'])){
			if($_POST['password'] != $_POST['password2']){
				show_message(array( 'msg' => '两次密码设置不一致' , 'code' => '-1'),'json');
			}
			$_POST['password'] = MD5(MD5($_POST['password'].KEY).KEY);
			$field = array('id','username','password','mobile','email','state','user_type');
		}else{
			$field = array('id','username','mobile','email','state','user_type');
		}
		$table = new table('admin');
		$res = $table
			  ->field($field)
			  ->type('id','auto_key')		//主键
			  ->type('username','unique')  	//唯一
			  ->other('add',array('add_time' => time() , 'update_time' => time() , 'pid' => $_SESSION['admin']['id']))  //添加的时候附加的值
			  ->other('update',array( 'update_time' => time()))						//更新的时候附加的值
			  ->commit();
		$data = $table->get_state();
		if($res){
			show_message(array('code' => '1' ,'msg' => '操作成功'),'json');
		}else{
			show_message(array('code' => '-1' ,'msg' => '操作成功'),'json');
		}
	}
	
	/*
	public function admin_list2(){
		$admin = M('admin')->where(array('id' => 1))->find();
		$admin['imagesab'] = '/uploads/default/20170715/1ab18430ae52a4c1ea8434fe451d4cb0.jpg,/uploads/default/20170715/b15c4b1a78f46eae4389ddb7ba60ca0b.jpg';
		$admin['imagesabc'] = '/uploads/default/20170715/1ab18430ae52a4c1ea8434fe451d4cb0.jpg,/uploads/default/20170715/b15c4b1a78f46eae4389ddb7ba60ca0b.jpg,/uploads/default/20170715/05b3119a66f4c3308e91f31ff983dba2.jpg';
		$admin['content1'] = '你好富文本';
		$admin['content2'] = '唐果';
		$admin['content3'] = '你好富文本唐果';
		$admin['start_time'] = '1491461251';
		$admin['end_time'] = '1491461251';
		self::form("",array(
			array('text','id','用户名'),
			array('password','password','密码'),
			array('label','password','标签'),
			array('textarea','username','用户名'),
			array('time','start_time','开始时间'),
			array('time','end_time','开始时间'),
			array('radio','id','单选',array('a','b','c')),
			array('checkbox','id','多选',array('aa','bb','cc')),
			array('selected','id','下拉',array('aa','bb','cc')),
			array('file','logo','图片',array('jpg','png','jpep','bmp'),array('style'=>'max-width:300px;max-height:300px;'),$size = 2,'upload_file.php'),
			array('file','logo2','图片',array('jpg','png','jpep','bmp'),'',$size = 2,'upload_file.php'),
			array('files','imagesab','文件',array('jpg','png','jpep','bmp'),$size=2,'upload_file.php'),
			array('files','imagesabc','文件',array('jpg','png','jpep','bmp'),$size=2,'upload_file.php'),
			array('editor','content1','描述'),
			array('editor','content2','描述'),
			array('editor','content3','描述'),
		),$admin,'post','public_form');
	}
	*/
}
?>