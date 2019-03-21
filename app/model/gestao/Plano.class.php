<?php
/**
	Active Record da CadNascimento
 */
class Plano extends TRecord{

    const TABLENAME = 'plano';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial} }

	public function __construct($id = NULL){
        
        parent::__construct($id);
        parent::addAttribute('tipo');
        parent::addAttribute('categoria');
        parent::addAttribute('tipodespesa');
    }
	
	

	
	
}
?>