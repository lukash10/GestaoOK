<?php
/**
 * Tabular report
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class RelatorioContaReceber extends TPage
{
    private $form; // form
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        // ------------------ Criação dos campos de formulario
        $this->form = new BootstrapFormBuilder('form_Customer_report');
        $this->form->setFormTitle(('Filtros para gerar relatório') );
        
        // ------------------ Criação dos campos de formulario
        $data_inicio         = new TDate('data_inicio');
        $data_fim            = new TDate('data_fim');
        $status_pagamento    = new TCombo('statuspagamento');
        $tipo_arq            = new TRadioGroup('tipo_arq');
        $cliente_id            = new TDBUniqueSearch('cliente_id','gestao','Cliente','id','nome');

        //------------------- Configuração do TCombo, com os tipos de status de pagamento
        $op_tipo = array();
        $op_tipo['Recebido'] = 'Recebido';
        $op_tipo['Pendente'] = 'Pendente';
        $status_pagamento-> addItems($op_tipo);
        
        $this->form->addFields( [new TLabel('Data de início')],     [$data_inicio],[new TLabel('Data de Fim')],[$data_fim] );
        $this->form->addFields( [new TLabel('Cliente')],     [$cliente_id] );
        $this->form->addFields( [new TLabel('Status do Pagamento')],     [$status_pagamento] );
        $this->form->addFields( [new TLabel('Arquivo de saída')],     [$tipo_arq] );

        // ------------------ Tamanho das fields:
        $data_inicio->setSize("50%");
        $data_fim->setSize("50%");
        $cliente_id->setSize("61.5%");
        $status_pagamento->setSize("61.5%");

        // ----------------- Mascaras das datas:
        $data_inicio->setMask('d-m-yyyy');
        $data_fim->setMask('d-m-yyyy');



        // ---------------------- Configurações do tipo de saída:
        $opcoes = ['html' =>'HTML', 'pdf' =>'PDF', 'rtf' =>'RTF', 'xls' =>'XLS'];
        $tipo_arq->setUseButton();
        $tipo_arq->addItems($opcoes);
        $tipo_arq->setValue('pdf');
        $tipo_arq->setLayout('horizontal');

        $cliente_id->setMinLength(1);
        
        $this->form->addAction( 'Gerar Arquivo', new TAction(array($this, 'onGenerate')), 'fa:download blue');
        
        // wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($this->form);
        
        parent::add($vbox);
    }

    public function formatDataBD($data){

        $dt = new DateTime($data);
        return $dt->format('Y-m-d');
    }


    public function onGenerate(){
                
        try
        {
            // open a transaction with database 'samples'
            TTransaction::open('gestao');
            
            // get the form data into an active record Customer
            $data = $this->form->getData();
            echo ($data->cliente_id);
            $repository = new TRepository('ContaReceber');
            $criteria   = new TCriteria;

            if($data->data_inicio and $data->data_fim){
                echo("okok");

                $criteria->add(new TFilter('dataemissao','BETWEEN',$this->formatDataBD($data->data_inicio),$this->formatDataBD($data->data_fim)));
            }
            if($data->statuspagamento){

                $criteria->add(new TFilter('statuspagamento','=',$data->statuspagamento));


            }

            if ($data->cliente_id){

                $criteria->add(new TFilter('cliente_id', '=', "{$data->cliente_id}"));
            }
           
            $customers = $repository->load($criteria);
            $format  = $data->tipo_arq;
            
            if ($customers)
            {
                $widths = array(20, 200, 300, 100, 100,100);
                
                switch ($format)
                {
                    case 'html':
                        $table = new TTableWriterHTML($widths);
                        break;
                    case 'pdf':
                        $table = new TTableWriterPDF($widths,'L');
                        break;
                    case 'rtf':
                        $table = new TTableWriterRTF($widths);
                        break;
                    case 'xls':
                        $table = new TTableWriterXLS($widths);
                        break;
                }
                
                if (!empty($table))
                {
                    // create the document styles
                    $table->addStyle('title', 'Arial', '10', '',    '#ffffff', '#607EFE');
                    $table->addStyle('datap', 'Arial', '6', '',    '#000000', '#ffffff');
                    $table->addStyle('datai', 'Arial', '6', '',    '#000000', '#ffffff');
                    $table->addStyle('header', 'Times', '16', 'BI', '#6F2828', '#FFF8D6');
                    $table->addStyle('footer', 'Times', '8', 'BI', '#000000', '#ffffff');

                    $table->setHeaderCallback( function($table) {
                        $table->addRow();
                        $table->addCell('Relatório de Contas a Receber', 'center', 'header', 6);
                        
                        $table->addRow();
                        $table->addCell('Id',      'center', 'title');
                        $table->addCell('Cliente/Fornecedor',      'left', 'title');
                        $table->addCell('Descrição',  'left', 'title');
                        $table->addCell('Data Emissão', 'center', 'title');
                        $table->addCell('Data Vencimento', 'center', 'title');
                        $table->addCell('Valor',     'center', 'title');
                    });

                    
                    // controls the background filling
                    $colour= FALSE;
                    
                    // data rows
                    $valor_total = 0;
                    foreach ($customers as $customer)
                    {
                        $style = $colour ? 'datap' : 'datai';
                        $table->addRow();
                        $table->addCell($customer->id,             'center',   $style);
                        $table->addCell(utf8_decode($customer->cliente->nome),             'left',   $style);
                        $table->addCell(utf8_decode($customer->descricao),             'left',   $style);
                        $table->addCell($this->formatData($customer->dataemissao),             'center',   $style);
                        $table->addCell($this->formatData($customer->datavencimento),             'center',   $style);
                        $table->addCell($this->formatValorDG($customer->valor),             'right',   $style);
                        $colour = !$colour;
                        $valor_total += $customer->valor;
                    }
                        $table->addRow();
                        $table->addCell("Total: ".$this->formatValorDG($valor_total), 'right', 'footer',6);
                    
                    $output = "app/output/relatoriocp.{$format}";
                    
                    // stores the file
                    if (!file_exists($output) OR is_writable($output))
                    {
                        $table->save($output);
                        parent::openFile($output);
                    }
                    else
                    {
                        throw new Exception(_t('Permission denied') . ': ' . $output);
                    }

                }
            } /// Caso não tenha nenhum resultado do select, retorna que não existe dados para os filtros.
            else
            {
                new TMessage('error', 'Não retornou nenhum dado com esta opção de filtro');
                
            }
    
            // fill the form with the active record data
            $this->form->setData($data);
            
            // close the transaction
            TTransaction::close();
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
        public function formatData($data){

        $dt = new DateTime($data);
        return $dt->format('d-m-Y');
    }
        public function formatValorDG($valor){

        $numero = number_format($valor,2,',','.');

        return $numero;
    }

}