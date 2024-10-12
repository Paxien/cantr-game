<?php

class OldLocNameManager extends AbstractManager
{
  public function getUsersnameById($id)
  {
    $sql = "SELECT usersname FROM oldlocnames WHERE id = :id";
    $stm = $this->db->prepare($sql);
    $stm->bindInt("id", $id);

    return $stm->executeScalar();
  }
}
