<?php
/**
 * Contas a Receber Formulário e Listagem
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class ContaReceberCadastro extends TPage
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

        $this->setDatabase('gestao');        // defines the database
        $this->setActiveRecord('ContaReceber');       // defines the active record
        $this->setDefaultOrder('id', 'desc'); // define the default order
        


        
        // create the form
        $this->form = new BootstrapFormBuilder('form_ContasReceberCad');
        $this->form->setFormTitle(('Área de Cadastro de Contas a Receber- Faculdade Anhanguera'));

        //Flitro
        $criteria = new TCriteria();
        $criteria->add(new TFilter("tipo","=","Receita"),  TExpression::AND_OPERATOR);
        
        // create the form fields
        $id = new THidden ('id');
        $descricao      = new TEntry('descricao');
        $valor      = new TEntry('valor');
        $datavencimento      = new TDate('datavencimento');
        $dataemissao      = new TDate('dataemissao');
        $statuspagamento      = new TCombo('statuspagamento');
        $plano = new TDBCombo('plano_id','gestao','Plano','id','categoria','id',$criteria);
        $cliente = new TUniqueSearch('cliente_id');

        //Setar a  quantidade minima de letras para ativar a pesquisa 
        $cliente->setMinLength(1);

        // open database transaction
        TTransaction::open('gestao');

        // load items from repository
        $collection = Cliente::all();
        
        // add the combo items
        $items = array();
        foreach ($collection as $object)
        {
            $items[$object->id] = $object->nome;
        }
        TTransaction::close();
        $cliente->addItems($items);

        $op_tipo = array();
        $op_tipo['Recebido'] = 'Recebido';
		$op_tipo['Pendente'] = 'Pendente';
        $statuspagamento-> addItems($op_tipo);     
        
        // add the form fields
        $this->form->addFields([new THidden(('ID'))], [$id]);
        $this->form->addFields([new TLabel(('Cliente'))], [$cliente]);
        $this->form->addFields([new TLabel(('Emissão'))], [$dataemissao], [new TLabel(('Vencimento'))], [$datavencimento] );
        $this->form->addFields([new TLabel(('Status Recebimento'))], [$statuspagamento],[new TLabel(('Plano de Conta'))], [$plano]);
        $this->form->addFields( [new TLabel(('Descrição do Recebimento'))], [$descricao], [new TLabel(('Valor Total'))], [$valor]  );
        
        // define the form actions
        $this->form->addAction( 'Salvar', new TAction([$this, 'onSave']), 'fa:save green');
        $this->form->addAction( 'Limpar Tela',new TAction([$this, 'onClear']), 'fa:eraser red');
        
        // make id not editable
        $statuspagamento->setSize('50%');
        $datavencimento->setSize('50%'); 
        $dataemissao->setSize('50%');
        $descricao->setSize('50%');
        $valor->setSize('50%');
        $cliente->setSize("80.7%",30);
        $plano->setSize("50%");

        //setando valores:
        $dataemissao->setValue(date('d-m-Y'));
        $valor->setValue("0,00");


        //mascara das datas:
        $dataemissao->setMask('dd-mm-yyyy');
        $datavencimento->setMask('dd-mm-yyyy');
        $valor->setNumericMask(2,',','.');

        //-----------------Inicio da datagrid

        //criando a datagrid:
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->with = "100%";

        //Colunas da datagrid:
        $col_id = new TDataGridColumn('id','Id','rigth','10%');
        $col_descricao = new TDataGridColumn('descricao','Descrição','rigth','20%');
        $col_cliente = new TDataGridColumn('cliente->nome','Fornecedor','rigth','30%');
        $col_valor = new TDataGridColumn("valor",'Valor','rigth','10%');
        $col_status = new TDataGridColumn('statuspagamento','Status do Pagamento','rigth','10%');
        $col_plano = new TDataGridColumn('plano->categoria','Plano de Conta','rigth','10%');
        $col_vencimento = new TDataGridColumn('datavencimento','Data do vencimento','rigth','10%');


        $this->datagrid->addColumn($col_id);
        $this->datagrid->addColumn($col_descricao);
        $this->datagrid->addColumn($col_cliente);
        $this->datagrid->addColumn($col_valor);
        $this->datagrid->addColumn($col_status);
        $this->datagrid->addColumn($col_plano);
        $this->datagrid->addColumn($col_vencimento);

        $col_vencimento->setTransformer(array($this,'formatDataDG'));
        $col_valor->setTransformer(array($this,'formatValorDG'));

        
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

        $this->datagrid->createModel();

        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($this->form);
        $vbox->add(TPanelGroup::pack('',$this->datagrid));
        
        // pack the table inside the page
        parent::add($vbox);
    }

    public function formatDataDG($col_vencimento,$object){

    	$date = new DateTime($object->datavencimento);
    	return $date->format('d-m-Y');
    }
    public function formatValorDG($col_valor,$object){
    	$numero = number_format($object->valor,2,',','.');

    	return "<span style='color:blue'>$numero</span>";
    }

    public function onSave($param){
        
        $param['valor'] = $this->formatValorBD($param['valor']);

        $param['dataemissao'] = $this->formatDataBD($param['dataemissao']);
        $param['datavencimento'] = $this->formatDataBD($param['datavencimento']);

        try{ 
            TTransaction::open('gestao'); // Abrir transação com banco de dados 
            
            // cria o novo objeto
            $object = new ContaReceber;
            $object->fromArray((array) $param);
            $object->store();

            new TMessage('info', 'Informações salvas com sucesso !');
            TTransaction::close(); // Closes the transaction 
            AdiantiCoreApplication::loadPage('ContaReceberCadastro');
        } 
        catch (Exception $e) 
        { 
            new TMessage('error', $e->getMessage()); 
        } 


    }

    public function formatDataBD($data){

        $dt = new DateTime($data);
        return $dt->format('Y-m-d');
    }

    public function formatValorBD($valor){

        $valor = str_replace(".","",$valor);
        $valor = str_replace(",",".",$valor);
        return $valor;

    }

    public function onEdit($param){
    	try {

    		if(isset($param['id'])){
    			$id = $param['id'];
    			
    			TTransaction::open('gestao');
    			$object = new ContaReceber($id);
    			$object->datavencimento = $this->formatDataDG($this,$object);

    			$date = new DateTime($object->dataemissao);
    			$object->dataemissao = $date->format('d-m-Y');
    			
    			$object->valor = number_format($object->valor,2,',','.');
    			$this->form->setData($object);
    			
    			TTransaction::close();
    		}else{
    			$this->form->clear();

    		}
    		
    	} catch (Exception $e) {
    		
    	}
    }
}