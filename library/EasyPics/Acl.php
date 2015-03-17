<?php
class EasyPics_Acl extends Zend_Acl
{

    public function __construct($file)
    {

        $roles = new Zend_Config_Ini($file, 'roles');
        $ressources = new Zend_Config_Ini($file, 'ressources');

        $roles = $roles->toArray();
        $ressources = $ressources->toArray();

    //	var_dump($roles);
        //var_dump($ressources);

        $this->setRoles($roles)->setRessources($ressources);

        foreach ($roles as $role => $parents) {

            //var_dump($role);

            $privileges = new Zend_Config_Ini($file, $role);

            //var_dump($privileges->toArray());

            $this->setPrivileges($role, $privileges);
        }
    }

    protected function setRoles($roles)
    {
        foreach ($roles as $role => $parents) {

            //var_dump($role);
            //var_dump($parents);

            if (empty($parents)) {
                $parents = null;
            } else {
                $parents = explode(',', $parents);
            }

            $this->addRole(new Zend_Acl_Role($role), $parents);
        }

        return $this;
    }

    protected function setRessources($ressources)
    {

        foreach ($ressources as $ressource => $parents)	{

            //var_dump($ressource);
            //var_dump($parents);

            if (empty($parents)) {
                $parents = null;
            }
            else {
                $parents = explode(',', $parents);
            }

            $this->add(new Zend_Acl_Resource($ressource), $parents);
        }
        return $this;
    }

    protected function setPrivileges($role, $privileges)
    {

        foreach ($privileges as $do => $ressources)	{
            foreach ($ressources as $ressource => $actions)	{
                if (empty($actions))	{
                    $actions = null;
                }
                else {
                    $actions = explode(',', $actions);
                }
                $this->{$do}($role, $ressource, $actions);
            }
        }
        return $this;
    }
}