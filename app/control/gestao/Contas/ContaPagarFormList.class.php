<?php
/**
 * Contas a Pagar Formulário e Listagem
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class ContaPagarFormList extends TPage
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
        $this->setActiveRecord('ContaPagar');       // defines the active record
        $this->setDefaultOrder('id', 'desc'); // define the default order
  
        // create the form
        $this->form = new BootstrapFormBuilder('form_ContasPagarCad');
        $this->form->setFormTitle(('Área de Cadastro de Contas a Pagar- Faculdade Anhanguera'));

        //Flitro
        $criteria = new TCriteria();
        $criteria->add(new TFilter("tipo","=","Despesa"),  TExpression::AND_OPERATOR);
        
        // create the form fields
        $id = new THidden ('id');
        $descricao      = new TEntry('descricao');
        $valor      = new TEntry('valor');
        $datavencimento      = new TDate('datavencimento');
        $dataemissao      = new TDate('dataemissao');
        $statuspagamento      = new TCombo('statuspagamento');
        //$statuspagamento = new TDBCombo('status_id','gestao','Status','id','NULL','NULL',);
        $plano = new TDBCombo('plano_id','gestao','Plano','id','categoria','id',$criteria);
        $cliente = new TUniqueSearch('cliente_id');
        $repetir      = new TSpinner('repetir');
        $tipodespesa      = new TCombo('tipodespesa');

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
        $op_tipo['Pago'] = 'Pago';
        $op_tipo['Pendente'] = 'Pendente';
        $statuspagamento-> addItems($op_tipo);
        
        $op_tipo2 = array();
        $op_tipo2['Variavel'] = 'Variável';
        $op_tipo2['Fixo'] = 'Fixo';
        $tipodespesa-> addItems($op_tipo2);    
        
        // add the form fields
        $this->form->addFields([new THidden(('ID'))], [$id]);
        $this->form->addFields([new TLabel(('Cliente'))], [$cliente]);
        $this->form->addFields([new TLabel(('Emissão'))], [$dataemissao], [new TLabel(('Vencimento'))], [$datavencimento] );
        $this->form->addFields([new TLabel(('Status Recebimento'))], [$statuspagamento],[new TLabel(('Plano de Conta'))], [$plano]);
        $this->form->addFields( [new TLabel(('Descrição do Recebimento'))], [$descricao], [new TLabel(('Valor Total'))], [$valor]  );
        $this->form->addFields( [new TLabel(('Quantidade de repetição por mês'))], [$repetir], [new TLabel(('Tipo Despesa'))], [$tipodespesa] );
        
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
        $tipodespesa->setSize("50%");
        $repetir->setRange(0,12,1);
        $repetir->setValue(0);

        //setando valores:
        $dataemissao->setValue(date('d-m-Y'));
        $valor->setValue("0,00");


        //mascara das datas:
        $dataemissao->setMask('dd-mm-yyyy');
        $datavencimento->setMask('dd-mm-yyyy');
        $valor->setNumericMask(2,',','.');

        //-------------------------------------------------------//
        //                                                       //   
        //                  |||||DATAGRID||||                    //
        //                                                       //
        //-------------------------------------------------------//


        //criando a datagrid:
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->with = "100%";

        //Colunas da datagrid:
        $col_id = new TDataGridColumn('id','Id','left','5%');
        $col_descricao = new TDataGridColumn('descricao','Descrição','left','25%');
        $col_cliente = new TDataGridColumn('cliente->nome','Fornecedor','left','20%');
        $col_valor = new TDataGridColumn("valor",'Valor','left','10%');
        $col_status = new TDataGridColumn('statuspagamento','Status do Pagamento','left','12%');
        $col_plano = new TDataGridColumn('plano->categoria','Plano de Conta','left','8%');
        $col_tipodespesa = new TDataGridColumn('tipodespesa','Tipo Despesa','left','10%');
        $col_vencimento = new TDataGridColumn('datavencimento','Data do vencimento','left','10%');
        
    
        $order1= new TAction(array($this, 'onReload'));
       // $order1->setParameter('order', 'id');
        $col_id->setAction($order1);

        $this->datagrid->addColumn($col_id);
        $this->datagrid->addColumn($col_descricao);
        $this->datagrid->addColumn($col_cliente);
        $this->datagrid->addColumn($col_valor);
        $this->datagrid->addColumn($col_status);
        $this->datagrid->addColumn($col_plano);
        $this->datagrid->addColumn($col_tipodespesa);
        $this->datagrid->addColumn($col_vencimento);
    

        $col_vencimento->setTransformer(array($this,'formatDataDG'));
        $col_valor->setTransformer(array($this,'formatValorDG'));
        $col_status->setTransformer(array($this,'corStatus'));
        $col_tipodespesa->setTransformer(array($this,'corStatus2'));
        $col_id->setAction( new TAction([$this, 'onReload']),   ['order' => 'id']);
        $col_status->setAction( new TAction([$this, 'onReload']),   ['order' => 'statuspagamento']);
        $col_tipodespesa->setAction( new TAction([$this, 'onReload']),   ['order' => 'tipodespesa']);
        
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

        /*** Cor do Status ***/

    public function corStatus($col_status,$object){

        if($object->statuspagamento == 'Pago'){
                return "<span style='color: white;line-height: 1;background-color: green;padding: .2em .6em .3em;padding-top: 0.2em;padding-right: 0.6em;padding-bottom: 0.3em;padding-left: 0.6em;border-radius: 0.25em;display: inline;'> $object->statuspagamento </span>";

        }
        return "<span style='color: white;line-height: 1;background-color: #ea4b38;padding: .2em .6em .3em;padding-top: 0.2em;padding-right: 0.6em;padding-bottom: 0.3em;padding-left: 0.6em;border-radius: 0.25em;display: inline;'> $object->statuspagamento </span>";
    }

    public function corStatus2($col_tipodespesa,$object){

        if($object->tipodespesa == 'Fixo'){
            return "<span style='color: white;line-height: 1;background-color: gray;padding: .2em .6em .3em;padding-top: 0.2em;padding-right: 0.6em;padding-bottom: 0.3em;padding-left: 0.6em;border-radius: 0.25em;display: inline;'> $object->tipodespesa</span>";
        }
        return "<span style='color: white;line-height: 1;background-color: blue;padding: .2em .6em .3em;padding-top: 0.2em;padding-right: 0.6em;padding-bottom: 0.3em;padding-left: 0.6em;border-radius: 0.25em;display: inline;'> $object->tipodespesa</span>";
    }




    public function formatDataDG($col_vencimento,$object){

        $date = new DateTime($object->datavencimento);
        return $date->format('d-m-Y');
    }
    public function formatValorDG($col_valor,$object){

        if($object->valor > 0){
            $object->valor = $object->valor*(-1);
        }
        $numero = number_format($object->valor,2,',','.');

        return "<span style='color:red'>$numero</span>";
    }

    public function onSave($param){
                      
        $param['valor'] = $this->formatValorBD($param['valor']);
        $param['dataemissao'] = $this->formatDataBD($param['dataemissao']);
        
        $test = $param['repetir'];
        $list = explode('-',$param['datavencimento']);
        
        for($i = 0; $i <= $test; $i++){
            $novadata = new DateTime();
            $novadata->setDate(intval($list[2]),intval($list[1]),intval($list[0]));
            $novadata->modify('+ '.$i.'month');
            $param['datavencimento'] = $novadata->format('d-m-Y');        

            $param['datavencimento'] = $this->formatDataBD($param['datavencimento']);

            try{ 
                TTransaction::open('gestao'); // Abrir transação com banco de dados 
                
                // cria o novo objeto
                $object = new ContaPagar;
                $object->fromArray((array) $param);
                $object->store();

                new TMessage('info', 'Informações salvas com sucesso !');
                TTransaction::close(); // Closes the transaction 
                AdiantiCoreApplication::loadPage('ContaPagarFormList');
            } 
            catch (Exception $e) 
            { 
                new TMessage('error', $e->getMessage()); 
            } 

        

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
                $object = new ContaPagar($id);
                $object->datavencimento = $this->formatDataDG($this,$object);

                $date = new DateTime($object->dataemissao);
                $object->dataemissao = $date->format('d-m-Y');
                
                 if($object->valor > 0){

                    $object->valor = $object->valor*(-1);
                }

                $object->valor = number_format(($object->valor),2,',','.');
                $this->form->setData($object);
                
                TTransaction::close();
            }else{
                $this->form->clear();

            }
            
        } catch (Exception $e) {
            
        }
    }
}