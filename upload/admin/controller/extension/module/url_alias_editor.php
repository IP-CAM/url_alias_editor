<?php
class ControllerExtensionModuleUrlAliasEditor extends Controller {
	private $message = array();

	public function index() {
		$this->load->language('extension/module/url_alias_editor');
		$this->document->setTitle($this->language->get('heading_title'));
        
        $this->load->model("extension/module/url_alias_editor");

        if(isset($this->request->post["action"]) && $this->validate()){
            $this->procedureData();
        }

		$data['heading_title'] = $this->language->get('heading_title');
        $data['text_limit'] = $this->language->get('text_limit');
        $data['text_delete_selected'] = $this->language->get('text_delete_selected');
        $data['text_rebuild_selected'] = $this->language->get('text_rebuild_selected');
        $data['text_query'] = $this->language->get('text_query');
        $data['text_alias'] = $this->language->get('text_alias');
        $data['text_global'] = $this->language->get('text_global');
        $data['text_rebuild_all'] = $this->language->get('text_rebuild_all');
        $data['text_rebuild_new'] = $this->language->get('text_rebuild_new');
        $data['text_delete_all'] = $this->language->get('text_delete_all');
        $data['text_check_collisions'] = $this->language->get('text_check_collisions');
        $data['text_delete_def'] = $this->language->get('text_delete_def');
        $data['text_show_all'] = $this->language->get('text_show_all');
        $data['text_add_manually'] = $this->language->get('text_add_manually');

        $data['warn_rebuild'] = $this->language->get('warn_rebuild');
        $data['warn_delete'] = $this->language->get('warn_delete');
        $data['warn_rebuild_all'] = $this->language->get('warn_rebuild_all');
        $data['warn_rebuild_new'] = $this->language->get('warn_rebuild_new');
        $data['warn_delete_all'] = $this->language->get('warn_delete_all');
        $data['warn_delete_def'] = $this->language->get('warn_delete_def');
        $data['warn_r_u_sure'] = $this->language->get('warn_r_u_sure');


		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('extension/module', 'token=' . $this->session->data['token'] . '&type=extension', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/url_alias_editor', 'token=' . $this->session->data['token'], true)
		);

		$data['action'] = $this->url->link('extension/module/url_alias_editor', 'token=' . $this->session->data['token'], true);

		$data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'] . '&type=extension', true);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
        }
        $limit = 50;

        if (isset($this->request->get['limit']) and in_array($this->request->get['limit'],array(50,100,150,200,300,500))) {
            $limit = $this->request->get['limit'];
		}

        $filter_data = array(
			'start'           => ($page - 1) * $limit,
			'limit'           => $limit
        );

        $data['limit'] = $limit;
        $data["collisions"] = $this->model_extension_module_url_alias_editor->getCollissions();
        
        if(isset($this->request->post["action"]) && $this->request->post["action"]=="checkCollisions"){
            $data["list"] = $this->model_extension_module_url_alias_editor->searchBy("keyword",$data["collisions"]);
            $data["listmode"] = "collisions";
            $product_total = count($data["list"]);
            if($product_total==0) $this->message["success"] = $this->language->get('text_no_collisions');
            else $this->message["danger"] = sprintf($this->language->get('text_collisions_f'),count($data["collisions"]));
        }else{
            if($data["collisions"]){
                $this->message["danger"] = sprintf($this->language->get('text_collisions_f'),count($data["collisions"]));
            }
            $product_total = $this->model_extension_module_url_alias_editor->getTotal();
            $data["list"] = $this->model_extension_module_url_alias_editor->getList($filter_data);
            $data["listmode"] = "fulllist";
        }

        $pagination = new Pagination();
		$pagination->total = $product_total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->url = $this->url->link('extension/module/url_alias_editor', 'token=' . $this->session->data['token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($product_total - $limit)) ? $product_total : ((($page - 1) * $limit) + $limit), $product_total, ceil($product_total / $limit));

        $data['message'] = $this->message;

		$this->response->setOutput($this->load->view('extension/module/url_alias_editor', $data));
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/url_alias_editor')) {
			$this->message['danger'] = $this->language->get('error_permission');
		}
		return !$this->message;
    }

    private function procedureData(){
        $r = true;

        if($this->request->post["action"]=="deleteSel"){

            $r = $this->model_extension_module_url_alias_editor->deleteAliases($this->request->post["sel"]);
            if($r) $this->message["success"] = sprintf($this->language->get('successful_delete'),count($this->request->post["sel"]));

        }elseif($this->request->post["action"]=="deleteGodDamnAllOfIt"){

            $product_total = $this->model_extension_module_url_alias_editor->getTotal();
            $r = $this->model_extension_module_url_alias_editor->deleteAliases(null,"all");
            if($r) $this->message["success"] = sprintf($this->language->get('successful_delete'),$product_total);

        }elseif($this->request->post["action"]=="deleteDefaults"){

            $product_total = $this->model_extension_module_url_alias_editor->getTotal();
            $r = $this->model_extension_module_url_alias_editor->deleteAliases(null,"def");
            $product_total -= $this->model_extension_module_url_alias_editor->getTotal();
            if($r) $this->message["success"] = sprintf($this->language->get('successful_delete'),$product_total);

        }elseif($this->request->post["action"]=="rebuildSel"){

            $this->load->helper("string");
            $c = 0;
            foreach($this->request->post["sel"] as $id){
                $newAlias = $this->getNewAliasForQuery($this->request->post["query"][$id],$id,true);
                if(!$newAlias) continue;
                $r = $this->model_extension_module_url_alias_editor->updateAlias($id,$newAlias);
                if(!$r) break;
                $c++;
            }
            if($c) $this->message["success"] = sprintf($this->language->get('successful_rebuilt'),$c);
            else $this->message["success"] = $this->language->get('affected_0');

        }elseif($this->request->post["action"]=="autoRebuildAll"){

            $this->load->helper("string");
            $data = $this->model_extension_module_url_alias_editor->getAllAliases();
            $c = 0;
            foreach($data as $row){
                $newAlias = $this->getNewAliasForQuery($row["query"],$row["url_alias_id"],false);
                if(!$newAlias) continue;
                $r = $this->model_extension_module_url_alias_editor->updateAlias($row["url_alias_id"],$newAlias);
                if(!$r) break;
                $c++;
            }
            if($c) $this->message["success"] = sprintf($this->language->get('successful_rebuilt'),$c);
            else $this->message["success"] = $this->language->get('affected_0');

        }elseif($this->request->post["action"]=="autoAddNew"){

            $this->load->helper("string");
            $data = $this->model_extension_module_url_alias_editor->getAllNewQueries();
            $c = 0;
            foreach($data as $key=>$arr){
                foreach($arr as $row){
                    $newAlias = $this->getNewAliasForTitle($row["name"]);
                    $r = $this->model_extension_module_url_alias_editor->createAlias($row["query"],$newAlias);
                    if(!$r) break;
                    $c++;
                }
                if(!$r) break;
            }
            if($c) $this->message["success"] = sprintf($this->language->get('successful_added'),$c);
            else $this->message["success"] = $this->language->get('affected_0');
            
        }elseif($this->request->post["action"]=="save"){

            $c = 0;
            foreach($this->request->post["query"] as $id=>$query){
                $alias = $this->request->post["keyword"][$id];
                if(!isset($alias) or strlen($alias)<4) continue;

                $index = "";
                if($this->model_extension_module_url_alias_editor->aliasExists($alias,$id,true)){
                    $index = 2;
                    while($this->model_extension_module_url_alias_editor->aliasExists("$alias$index",$id,true)) $index++;
                }
                $r = $this->model_extension_module_url_alias_editor->updateAlias($id,"$alias$index",$query);
                if(!$r) break;
                $c++;
            }
            if(isset($this->request->post["newquery"])){
                var_dump($this->request->post);
                foreach($this->request->post["newquery"] as $id=>$query){
                    $alias = $this->request->post["newkeyword"][$id];
                    if(!isset($alias) or strlen($alias)<4) continue;
                    $index = "";
                    if($this->model_extension_module_url_alias_editor->aliasExists($alias,0,true)){
                        $index = 2;
                        while($this->model_extension_module_url_alias_editor->aliasExists("$alias$index",0,true)) $index++;
                    }
                    $r = $this->model_extension_module_url_alias_editor->createAlias($query,$alias);
                    if(!$r) break;
                    $c++;
                }
            }
            if($c) $this->message["success"] = sprintf($this->language->get('successful_saved'),$c);
            else $this->message["success"] = $this->language->get('affected_0');

        }
        if(!$r) $this->message["danger"] = $this->language->get('error_saving_failed');
    }

    private function getNewAliasForQuery($url,$id,$refresh=true){
        $string = new StringHelper();
        $name = $this->model_extension_module_url_alias_editor->getPageName($url);
        $this->analyzeAndCutName($name);
        $name = preg_replace("/[^a-z\_0-9]/","",
                preg_replace("/[\-\.]/","_",
                $string->cutDoubling(
                $string->translit($name)
                )));
        $index = "";
        if($this->model_extension_module_url_alias_editor->aliasExists($name,$id,$refresh)){
            $index = 2;
            while($this->model_extension_module_url_alias_editor->aliasExists("$name$index",$id,$refresh)) $index++;
        }
        return "$name$index";
    }
    private function getNewAliasForTitle($title){
        $string = new StringHelper();
        $this->analyzeAndCutName($title);
        $title = preg_replace("/[^a-z\_0-9]/","",
                preg_replace("/[\-\.]/","_",
                $string->cutDoubling(
                $string->translit($title)
                )));
        $index = "";
        if($this->model_extension_module_url_alias_editor->aliasExists($title,0,true)){
            $index = 2;
            while($this->model_extension_module_url_alias_editor->aliasExists("$title$index",0,true)) $index++;
        }
        return "$title$index";
    }
    private function analyzeAndCutName(&$name,$wordLimit = 5){
        $a = explode(" ",$name);
        if(count($a)>$wordLimit) $name = implode(" ",array_chunk($a,$wordLimit)[0]);
    }
}