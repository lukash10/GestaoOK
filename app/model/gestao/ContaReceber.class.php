<?php
/**
	Active Record da CadNascimento
 */
class ContaReceber extends TRecord{

    const TABLENAME = 'contareceber';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial} }

    private $cliente;

	public function __construct($id = NULL){
        
        parent::__construct($id);
        parent::addAttribute('descricao');
        parent::addAttribute('datavencimento');
        parent::addAttribute('valor');
        parent::addAttribute('statuspagamento');
        parent::addAttribute('cliente_id');
        parent::addAttribute('plano_id');
        parent::addAttribute('dataemissao');
    }

    public function set_cliente(Cliente $object){
        $this->cliente = $object;
        $this->cliente_id = $object->id;
    }

    public function get_Cliente(){

        if(empty($this->cliente)){
            
            $this->cliente = new Cliente ($this->cliente_id);

        }

        return $this->cliente;
        

    }


    public function set_plano(Plano $object){
        $this->plano = $object;
        $this->plano_id = $object->id;
    }

    public function get_Plano(){

        if(empty($this->plano)){
            
            $this->plano = new Plano ($this->plano_id);

        }

        return $this->plano;
        

    }
	

	
	
}
?>