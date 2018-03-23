<?php

/**
 * Kolab Tags backend
 *
 * @author Aleksander Machniak <machniak@kolabsys.com>
 *
 * Copyright (C) 2014, Kolab Systems AG <contact@kolabsys.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

class kolab_tags_backend
{
    private $tag_cols = array('name', 'category', 'color', 'parent', 'iconName', 'priority', 'members');

    const O_TYPE     = 'relation';
    const O_CATEGORY = 'tag';
    private $db_tags = 'tags';
    private $db_tag2mail = 'tag2mail';

    /**
     * Tags list
     *
     * @param array $filter Search filter
     *
     * @return array List of tags
     */

     public function __construct($plugin)
     {
         $this->rc = $plugin->rc;
         $this->plugin = $plugin;
         $db = $this->rc->get_dbh();
         $this->db_tags = $this->rc->config->get('db_tags', $db->table_name($this->db_tags));
         $this->db_tag2mail = $this->rc->config->get('db_tag2mail', $db->table_name($this->db_tag2mail));
         $this->tags = array();
         // read database config
        // $db = $this->rc->get_dbh();
        // $this->db_lists = $this->rc->config->get('db_table_lists', $db->table_name($this->db_lists));
        // $this->db_tasks = $this->rc->config->get('db_table_tasks', $db->table_name($this->db_tasks));

        // $this->_read_lists();
     }

    public function list_tags($filter = array())
    {
      $filtre = '';
      for($i=0;$i<count($filter);$i++){
        $filtre .= ' AND '.$filter[$i][0].' '.$filter[$i][1].' \''.$filter[$i][2][0].'\'';
      }
      $result = $this->rc->db->query(sprintf(
          "SELECT * FROM $this->db_tags
           WHERE id_user = ?".$filtre
         ),
         $this->rc->user->ID
     );
    while ($result && ($arr = $this->rc->db->fetch_assoc($result))) {
        $tags[] = $arr;
    }

      return $tags;
    }
    public function list_tags_with_mail($filter = array())
    {
      $filtre = '';
      for($i=0;$i<count($filter);$i++){
        $filtre .= ' AND '.$filter[$i][0].' '.$filter[$i][1].' \''.$filter[$i][2][0].'\'';
      }
      $result = $this->rc->db->query(sprintf(
          "SELECT * FROM $this->db_tags a, $this->db_tag2mail b WHERE a.uid = b.uid and b.id_user = ?".$filtre
         ),
         $this->rc->user->ID
     );
    while ($result && ($arr = $this->rc->db->fetch_assoc($result))) {
        $tags[] = $arr;
    }

      return $tags;
    }

    /**
     * Create tag object
     *
     * @param array $tag Tag data
     *
     * @return boolean|array Tag data on success, False on failure
     */
    public function create($tag)
    {
        $tag_uid = $tag['uid'];
        $tag    = array_intersect_key($tag, array_combine($this->tag_cols, $this->tag_cols));
        $tag['category'] = self::O_CATEGORY;
        if(!isset($tag['color']) or empty($tag['color'])) $tag['color'] = '#ccc';
        $result = $this->rc->db->query(sprintf(
            "INSERT INTO `$this->db_tags`( `id_user`, `name`, `color`, `categorie`) VALUES (?,?,?,?)"
            ),
          $this->rc->user->ID,
          $tag['name'],
          $tag['color'],
          $tag['category']
        );

      return $result ? $tag : false;
    }

    /**
     * Update tag object
     *
     * @param array $tag Tag data
     *
     * @return boolean|array Tag data on success, False on failure
     */
    public function update($tag)
    {
          //  error_log('ffff');

        // get tag object data, we need _mailbox
        //error_log(print_r($tag['uid'],1));
        $list    = $this->list_tags(array(array('uid', '=', $tag['uid'])));

        $old_tag = $list[0];

        if (!$old_tag) {
          //  return false;
        }
        if(isset($tag['folder']) or isset($tag['uidMessage'])){
        if(!isset($tag['folder']) or empty($tag['folder'])) $tag['folder'] = '';
        if(is_array($tag['uidMessage'])) $tag['uidMessage'] = $tag['uidMessage'][0];
        for($i=0;$i<count($tag['uidMessage']);$i++){
          if(is_array($tag['uidMessage'])) $temp = $tag['uidMessage'][$i];
          else $temp = $tag['uidMessage'];
          $result=$this->rc->db->query(sprintf("INSERT INTO `$this->db_tag2mail`( `id_user`, `uid`, `member`,`folder`) VALUES (?,?,?,?)"),
         $this->rc->user->ID,
         $tag['uid'],
         $temp,
         $tag['folder']
          );

        }
        return $result ? $tag : false;
      } else {

        $result = $result=$this->rc->db->query(sprintf("UPDATE `$this->db_tags` SET `name`=?,`color`=? WHERE `uid`=? and `id_user`=?"),
          $tag['name'],
          $tag['color'],
          $tag['uid'],
          $this->rc->user->ID
        );

          return $result ? $tag : false;
      }

    }

    /**
     * Remove tag object
     *
     * @param string $uid Object unique identifier
     *
     * @return boolean True on success, False on failure
     */
    public function remove($uid)
    {
      if(is_array($uid)){
        if(isset($uid['uid']) and isset($uid['folder']) and isset($uid['member'])){
      //    error_log(print_r($uid,1));
          $result = $this->rc->db->query(sprintf("DELETE FROM `$this->db_tag2mail` WHERE `uid`=? and `id_user`=? and member = ? and folder = ?"),
            $uid['uid'],
            $this->rc->user->ID,
            $uid['member'],
            $uid['folder']
          );
            return $result ? $tag : false;
        } else {
            return false;
        }
      }else{
        if(isset($uid) and !empty($uid)){
      //    error_log(print_r($uid,1));
          $result = $this->rc->db->query(sprintf("DELETE FROM `$this->db_tags` WHERE `uid`=? and `id_user`=?"),
            $uid,
            $this->rc->user->ID
          );
            return true;
        } else {
            return false;
        }
      }
    }
}
