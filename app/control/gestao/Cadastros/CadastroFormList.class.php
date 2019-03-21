<?php
/**
 * Listagem Cadastros
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class CadastroFormList extends TPage
{
    protected $form;      // form
    protected $datagrid;  // datagrid
    protected $loaded;
    protected $pageNavigation;  // pagination component
    
    // trait with onSave, onEdit, onDelete, onReload, onSearch...
    use Adianti\Base\AdiantiStandardFormListTrait;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct(){
        parent::__construct();
        
        $this->setDatabase('gestao'); // define the database
        $this->setActiveRecord('Cliente'); // define the Active Record
        $this->setDefaultOrder('id', 'asc'); // define the default order
        $this->setLimit(-1); // turn off limit for datagrid
        
        // create the form
        $this->form = new BootstrapFormBuilder('form_CadastroList');
        $this->form->setFormTitle(('Ficha Cadastral'));
        
        // create the form fields
        $id = new THidden ('id');
        //informações do cliente/fonercedor;
        $nome    	   = new TEntry('nome');
		$telefone      = new TEntry('telefone');
		$rua     	   = new TEntry('rua');
		$bairro		   = new TEntry('bairro');
		$cep		   = new TEntry('cep');
		$cpf 		   = new TEntry('cpf');
		$cidade 	   = new TEntry('cidade');

		//Tipo(cliente, forncecedor ou funcionario)
		$tipo      = new TRadioGroup('tipo');

		//Tipo de Pessoa: Pessoa Juridica ou fisica
		$pessoa['j'] = 'Pessoa Juridica';
		$pessoa['f'] = 'Pessoa Fisica';
		$tipo->setLayout('horizontal');

		

		//array para o tipo de cliente.
        $op_tipo = array();
        $op_tipo['Cliente'] = 'Cliente';
		$op_tipo['Fornecedor'] = 'Fornecedor';
        $tipo-> addItems($op_tipo);


        $label = new TLabel('Informações Pessoais', '#DD8D16', 12, 'bi');
        $label->style='text-align:left;border-bottom:1px solid #c0c0c0;width:100%';
        $this->form->addContent( [$label] );
        $this->form->addFields( [new TLabel(('Nome / Razão Social'))], [$nome]);
        $this->form->addFields( [new TLabel(('CPF'))], [$cpf]);
		$this->form->addFields( [new TLabel(('Telefone'))], [$telefone] );

        $this->form->addFields( [new TLabel(('Tipo'))], [$tipo] );
        $this->form->addFields( [new THidden(('ID'))], [$id] );

		$label = new TLabel('Endereço', '#DD8D16', 12, 'bi');
        $label->style='text-align:left;border-bottom:1px solid #c0c0c0;width:100%';
        $this->form->addContent( [$label] );
        $this->form->addFields( [new TLabel(('Rua'))], [$rua],[new TLabel(('Bairro'))], [$bairro]);
        $this->form->addFields([new TLabel(('Cidade'))], [$cidade], [new TLabel(('CEP'))], [$cep]);




		$nome->setsize ('50%');
		$cpf->setsize("50%");
		$telefone->setsize ('20%');

		//mascaras:
		$telefone->setMask ('(99)9.9999-9999');
		$cpf->setMask('999.999.999-99');
		$cep->setMask('99999-999');
		
        

        
        // define the form actions
        $this->form->addAction( 'Salvar', new TAction([$this, 'onSave']), 'fa:save green');
        $this->form->addAction( 'Limpar',new TAction([$this, 'onClear']), 'fa:eraser red');
        
        // make id not editable
        $id->setEditable(FALSE);
        
        // create the datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';
        
        // add the columns
        $col_id    = new TDataGridColumn('id', 'Id', 'left', '');
        $col_nome  = new TDataGridColumn('nome', 'Nome', 'left', '30%');
        $col_tipo  = new TDataGridColumn('tipo', 'Tipo', 'left', '');
        $col_telefone  = new TDataGridColumn('telefone', 'Telefone', 'left', '');

        
        $this->datagrid->addColumn($col_id);
        $this->datagrid->addColumn($col_nome);
        $this->datagrid->addColumn($col_tipo);  
        $this->datagrid->addColumn($col_telefone);

        
        $col_id->setAction( new TAction([$this, 'onReload']),   ['order' => 'id']);
        
        // add the actions
        $action1 = new TDataGridAction([$this, 'onEdit']);
        $action1->setLabel('Edit');
        $action1->setImage('fa:edit blue');
        $action1->setFields(['id']);
        $this->datagrid->addAction($action1);
        
        $action2 = new TDataGridAction([$this, 'onDelete']);
        $action2->setLabel('Delete');
        $action2->setImage('fa:trash red');
        $action2->setFields(['id']);
        $this->datagrid->addAction($action2);
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // wrap objects inside a table
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($this->form);
        $vbox->add(TPanelGroup::pack('', $this->datagrid));
        
        // pack the table inside the page
        parent::add($vbox);
    }
    public static function onSave($param){
        try{
            if(!($param['id'])){

            
                TTransaction::open('gestao'); // open transaction 
                
                // create a new object
                $object = new Cliente;
                $object->fromArray((array) $param);
                $object->store() ;

                
                new TMessage('info', 'Object stored successfully');
                TTransaction::close(); // Closes the transaction 
                AdiantiCoreApplication::loadPage('CadastroFormList');
            

            }else{
                echo($param['id']);
                TTransaction::open('gestao');
                $object = new Cliente($param['id']);

                if($object){

                    $object->nome = $param['nome'];
                    $object->telefone = $param['telefone'];
                    $object->rua = $param['rua'];
                    $object->bairro = $param['bairro'];
                    $object->cep = $param['cep'];
                    $object->cpf = $param['cpf'];
                    $object->cidade = $param['cidade'];
                    $object->tipo = $param['tipo'];
                    $object->store();

                }
                
                TTransaction::close();
                AdiantiCoreApplication::loadPage('CadastroFormList');

            }
        } 
    catch (Exception $e){ 
        new TMessage('error', $e->getMessage()); 
    } 
    }


}
