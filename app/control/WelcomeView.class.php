<?php
/**
 * WelcomeView
 *
 * @version    1.0
 * @package    control
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class WelcomeView extends TPage
{
    /**
     * Class constructor
     * Creates the page
     */
    function __construct()
    {
        parent::__construct();

        $saldoMes = [
            'saldo' => $this->saldoMes(),
            'contaReceber' => $this->contaReceber(),
            'contaPagar' => $this->contaPagar(),
        ];

        $template = new THtmlRenderer('app/resources/dashboard.html');
        $template->enableSection('main', $saldoMes);


        $html = new THtmlRenderer('app/resources/google_bar_chart.html');
        
        TTransaction::open('gestao');

        

        $data = array();
        $data[] = [ '', 'Contas a Receber', 'Contas a Pagar'];
        $data[] = [ 'Janeiro',   100,       100];
        $data[] = [ 'Fevereiro',   0,       140];
        $data[] = [ 'Março',   0,       0];
        $data[] = [ 'Abril',   0,       0];
        $data[] = [ 'Maio',   0,       0];
        $data[] = [ 'Junho',   0,       0];
        $data[] = [ 'Julho',   0,       0];
        $data[] = [ 'Agosto',   0,       0];
        $data[] = [ 'Setembro',   0,       0];
        $data[] = [ 'Outubro',   0,       0];
        $data[] = [ 'Novembro',   0,       0];
        $data[] = [ 'Dezembro',   0,       0];
        
        # PS: If you use values from database ($row['total'), 
        # cast to float. Ex: (float) $row['total']
        
        $panel = new TPanelGroup('Relatório Anual - Anhanguera');
        $panel->style = 'width: 100%;text-align: center;line-height:1';
        //$panel->style = 'text-align: center';
        $panel->add($html);
        
        // replace the main section variables
        $html->enableSection('main', array('data'   => json_encode($data),
                                           'width'  => '100%',
                                           'height'  => '300px',
                                           'title'  => 'Relatório de Dados',
                                           'ytitle' => 'Valor', 
                                           'xtitle' => 'Dias',
                                           'uniqid' => uniqid()));
        
        TTransaction::close();
        // add the template to the page
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add($panel);

        parent::add($template);
        parent::add($container);

   

    }
    
    public function formatValorDG($valor){

    	$numero = number_format($valor,2,',','.');
    	return $numero;
    }

    public function contaRecebida(){

    	TTransaction::open('gestao');
        $conn = TTransaction::get();

        

        $sth = $conn->prepare("select valor from contareceber where MONTH(dataemissao)=MONTH( NOW() ) and statuspagamento = 'Recebido'");
    	//$sth = $conn->prepare("select valor from contareceber where strftime('%m',dataemissao)=strftime('%m','now') and statuspagamento = 'Recebido'");
        $sth->execute();
        $result = $sth->fetchAll();

        $valor_recebido = 0;
        foreach ($result as $row) {
        	
        	$valor_recebido += $row['valor'];
        }
        return ($this->formatValorDG($valor_recebido));

        TTransaction::close();
    }

    public function contaReceber(){

    	TTransaction::open('gestao');
        $conn = TTransaction::get();

    	$sth = $conn->prepare("select valor from contareceber where MONTH(dataemissao)= MONTH( NOW() ) and statuspagamento = 'Pendente'");
        $sth->execute();
        $result = $sth->fetchAll();

        $valor_recebido = 0;
        foreach ($result as $row) {
        	
        	$valor_recebido += $row['valor'];
        }
        return ($this->formatValorDG($valor_recebido));

        TTransaction::close();
    }

    public function contaPagar(){

    	TTransaction::open('gestao');
        $conn = TTransaction::get();

     	$sth = $conn->prepare("select valor from contapagar where MONTH(dataemissao)= MONTH( NOW() ) and statuspagamento = 'Pendente'");
        $sth->execute();
        $result = $sth->fetchAll();

        $valor_recebido = 0;
        foreach ($result as $row) {
        	
        	$valor_recebido += $row['valor'];
        }
        return ($this->formatValorDG($valor_recebido));

        TTransaction::close();
    }
    public function contaPaga(){

    	TTransaction::open('gestao');
        $conn = TTransaction::get();

    	$sth = $conn->prepare("select valor from contapagar where MONTH(dataemissao)= MONTH( NOW() ) and statuspagamento = 'Pago'");
        $sth->execute();
        $result = $sth->fetchAll();

        $valor_recebido = 0;
        foreach ($result as $row) {
        	
        	$valor_recebido += $row['valor'];
        }
        return ($this->formatValorDG($valor_recebido));

        TTransaction::close();
    }

    //Saldo mês:

    public function saldoMes(){

    	return $this->formatValorDG((float)$this->contaRecebida()-(float)$this->contaPaga());
    }


}
