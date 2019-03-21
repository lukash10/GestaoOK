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
class CadastroNaturezaForm extends TPage
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
        $this->setActiveRecord('Plano'); // define the Active Record
        $this->setDefaultOrder('id', 'asc'); // define the default order
        $this->setLimit(-1); // turn off limit for datagrid
        
        // create the form
        $this->form = new BootstrapFormBuilder('form_CadastroList');
        $this->form->setFormTitle(('Área de Cadastro de Plano de Contas - Faculdade Anhanguera'));
        
        // create the form fields
        $id = new THidden ('id');
        $categoria      = new TEntry('categoria');
        $tipo			= new TCombo('tipo');
        $tipodespesa	= new TCombo('tipodespesa');

        $op_tipo = array();
        $op_tipo['Receita'] = 'Receita';
        $op_tipo['Despesa'] = 'Despesa';
        $tipo->addItems($op_tipo);

        $op_tipo2 = array();
        $op_tipo2['Variavel'] = 'Variável';
        $op_tipo2['Fixo'] = 'Fixo';
        $tipodespesa->addItems($op_tipo2);

        
        // add the form fields
		$this->form->addFields( [new TLabel(('Descrição da Categoria'))], [$categoria]);
        $this->form->addFields([new TLabel(("Tipo de Conta"))], [$tipo]);
        $this->form->addFields([new TLabel(("Tipo Despesa"))], [$tipodespesa]);
        $this->form->addFields([new THidden(("ID"))], [$id]);
        $tipo->enableSearch();
        $tipodespesa->setSize ('15%');
        $tipo->setSize ('15%');
        $categoria->setSize ('30%');
        // define the form actions
        $this->form->addAction( 'Salvar', new TAction([$this, 'onSave']), 'fa:save green');
        $this->form->addAction( 'Limpar',new TAction([$this, 'onClear']), 'fa:eraser red');
        
        // make id not editable
        $id->setEditable(FALSE);
        
        // create the datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';
        
        // add the columns
        $col_id    = new TDataGridColumn('id', 'Id', 'left', '10%');
        $col_categoria  = new TDataGridColumn('categoria', 'Categoria', 'left', '');
        $col_tipo    = new TDataGridColumn('tipo', 'Tipo de Conta', 'left', '');
        $col_tipodespesa    = new TDataGridColumn('tipodespesa', 'Tipo Despesa', 'left', '');

        $this->datagrid->addColumn($col_id);
        $this->datagrid->addColumn($col_categoria);  
        $this->datagrid->addColumn($col_tipo);
        $this->datagrid->addColumn($col_tipodespesa);

        $col_tipo->setTransformer(array($this,'corStatus'));
        $col_tipo->setAction( new TAction([$this, 'onReload']),   ['order' => 'tipo']);
        $col_tipodespesa->setAction( new TAction([$this, 'onReload']),   ['order' => 'tipodespesa']);
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

    public function corStatus($col_tipo,$object){

        if($object->tipo == 'Receita'){
                return "<span style='color: white;line-height: 1;background-color: green;padding: .2em .6em .3em;padding-top: 0.2em;padding-right: 0.6em;padding-bottom: 0.3em;padding-left: 0.6em;border-radius: 0.25em;display: inline;'> $object->tipo</span>";

        }
        return "<span style='color: white;line-height: 1;background-color: #ea4b38;padding: .2em .6em .3em;padding-top: 0.2em;padding-right: 0.6em;padding-bottom: 0.3em;padding-left: 0.6em;border-radius: 0.25em;display: inline;'> $object->tipo</span>";
    }

    public static function onSave($param){
        
        if(!($param['id'])){

        try{ 
            TTransaction::open('gestao'); // open transaction 
            
            // create a new object
            $object = new Plano;
            $object->fromArray((array) $param);
            $object->store() ;

            
            new TMessage('info', 'Object stored successfully');
            TTransaction::close(); // Closes the transaction 
            AdiantiCoreApplication::loadPage('CadastroNaturezaForm');
        } 
        catch (Exception $e) 
        { 
            new TMessage('error', $e->getMessage()); 
        } 

        }else{
            TTransaction::open('gestao');
            $object = new Plano($param['id']);

            if($object){
                $object->tipodespesa = $param['tipodespesa'];
                $object->tipo = $param['tipo'];
                $object->categoria = $param['categoria'];
                $object->store();

            }

            TTransaction::close();
            AdiantiCoreApplication::loadPage('CadastroNaturezaForm');
        }
    }

}
