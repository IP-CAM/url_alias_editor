<?php
class ModelExtensionModuleUrlAliasEditor extends Model {
    private $table = DB_PREFIX."url_alias";
    public function getList($data) {
        return $this->db->query("SELECT * FROM `$this->table` ORDER BY `url_alias_id` ASC LIMIT " . (int)$data['start'] . "," . (int)$data['limit'])->rows;
    }
    public function getTotal() {
        $query = $this->db->query("SELECT count(*) as cnt FROM `$this->table`");
        return $query->row ? $query->row["cnt"] : 0;
    }
    public function getCollissions(){
        return array_column(
            array_filter(
                $this->db->query("SELECT keyword, count(url_alias_id) as cnt FROM `$this->table` GROUP BY keyword ORDER BY cnt DESC")->rows,
                function($r){
                    return $r["cnt"]>1;
                }
            ),"keyword"
        );
    }
    public function searchBy($fieldName,$list = array()){
        $fieldName = $this->db->escape($fieldName);
        $list = array_map(function($v){ 
            return $this->db->escape($v);
        },$list);
        return $this->db->query("SELECT * FROM `$this->table` WHERE $fieldName in ('".implode("','",$list)."') ORDER BY `url_alias_id` ASC")->rows;
    }
    public function getAllAliases(){
        return $this->db->query("SELECT * FROM `$this->table` ORDER BY `url_alias_id` ASC")->rows;
    }
    public function getAllNewQueries(){
        return array(
            "product"       => $this->db->query("SELECT CONCAT_WS(' ',m.name,p.model) as `name`, CONCAT('product_id=',p.product_id) as query FROM ".DB_PREFIX."product p 
            LEFT JOIN ".DB_PREFIX."manufacturer m ON p.manufacturer_id=m.manufacturer_id 
            LEFT JOIN `$this->table` u ON u.query=CONCAT('product_id=',p.product_id) 
            WHERE status=1 AND u.keyword IS NULL")->rows,

            "category"      => $this->db->query("SELECT `name`, CONCAT('category_id=',c.category_id) as query FROM ".DB_PREFIX."category c 
            LEFT JOIN ".DB_PREFIX."category_description d ON d.category_id=c.category_id 
            LEFT JOIN `$this->table` u ON u.query=CONCAT('category_id=',c.category_id) 
            WHERE c.status=1 AND u.keyword IS NULL")->rows,

            "manufacturer"  => $this->db->query("SELECT `name`, CONCAT('manufacturer_id=',manufacturer_id) as query FROM ".DB_PREFIX."manufacturer m 
            LEFT JOIN `$this->table` u ON u.query=CONCAT('manufacturer_id=',m.manufacturer_id)
            WHERE u.keyword IS NULL")->rows,

            "information"   => $this->db->query("SELECT title as `name`, CONCAT('information_id=',i.information_id) as query FROM ".DB_PREFIX."information i 
            LEFT JOIN ".DB_PREFIX."information_description d ON d.information_id=i.information_id 
            LEFT JOIN `$this->table` u ON u.query=CONCAT('information_id=',i.information_id)
            WHERE i.status=1 AND u.keyword IS NULL")->rows,
        );
    }
    public function aliasExists($alias,$id,$refresh){
        $id = (int)$id;
        if($refresh){
            $SQL = "SELECT count(url_alias_id) as cnt FROM `$this->table` WHERE keyword='".$this->db->escape($alias)."' AND url_alias_id!=$id";
        }else{
            $SQL = "SELECT count(url_alias_id) as cnt FROM `$this->table` WHERE keyword='".$this->db->escape($alias)."' AND url_alias_id<$id";
        }
        return $this->db->query($SQL)->row["cnt"] > 0;
    }
    public function getPageName($query){
        $table = str_replace("_id","",preg_replace("/[^a-z\_]/","",$query));
        $query = $this->db->escape($query);
        $field = "name";
        if($table=="information") $field = "title";
        if($table=="product" || $table=="category" || $table=="information"){
            $table .= "_description";
        }else{
            if($table!="manufacturer"){
                if(!$this->db->query("SHOW TABLES LIKE '".DB_PREFIX."$table'")->rows){
                    return false;
                }
            }
        }
        $table = DB_PREFIX.$table;
        $row = $this->db->query("SELECT `$field` FROM `$table` WHERE $query")->row;

        return $row?$row[$field]:"";
    }




    public function deleteAliases($ids=array(),$mode="chunk"){
        if($ids && count($ids)>0 && $mode=="chunk"){
            $ids = array_map('intval', $ids);
            $SQL = "DELETE FROM `$this->table` WHERE url_alias_id IN (".implode(",",$ids).") LIMIT ".count($ids);
            
            $this->db->query($SQL);
            return true;
        }elseif($ids===null && $mode=="all"){
            $SQL = "TRUNCATE TABLE `$this->table`";

            $this->db->query($SQL);
            return true;
        }elseif($ids===null && $mode=="def"){
            $SQL = "DELETE FROM `$this->table` WHERE query LIKE 'product_id=%' or query LIKE 'category_id=%' or query LIKE 'information_id=%' or query LIKE 'manufacturer_id=%'";

            $this->db->query($SQL);
            return true;
        }
        return false;
    }
    public function updateAlias($id,$alias,$query=null){
        $id = (int)$id;
        $alias = $this->db->escape($alias);
        if(!$alias) return false;

        $SQL = "UPDATE `$this->table` SET keyword='$alias'";
        if($query) $SQL .= ",query='".$this->db->escape($query)."'";
        $SQL .= " WHERE url_alias_id=$id LIMIT 1";

        $this->db->query($SQL);
        return true;
    }
    public function createAlias($query,$alias){
        $query = $this->db->escape($query);
        $alias = $this->db->escape($alias);
        if(!$alias or !$query) return false;
        $SQL = "INSERT INTO `$this->table` SET query='$query', keyword='$alias'";

        $this->db->query($SQL);
        return true;
    }
}