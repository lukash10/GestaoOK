<?php
/**
	Active Record da CadNascimento
 */
class Cliente extends TRecord{

    const TABLENAME = 'cliente';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial} }

	public function __construct($id = NULL){
        
        parent::__construct($id);
        parent::addAttribute('nome');
        parent::addAttribute('telefone');
        parent::addAttribute('rua');
        parent::addAttribute('bairro');
        parent::addAttribute('cep');
        parent::addAttribute('cpf');
        parent::addAttribute('cidade');
        parent::addAttribute('tipo');
        parent::addAttribute('radio');
    }
	
	

	
	
}
?>
