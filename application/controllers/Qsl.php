<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* 

	Handles all functions todo with QSLing.

*/

class qsl extends CI_Controller {

        /* Controls who can access the controller and its functions */
        function __construct()
        {
                parent::__construct();
                $this->load->helper(array('form', 'url'));

                $this->load->model('user_model');
                if(!$this->user_model->authorize(2)) { $this->session->set_flashdata('notice', 'You\'re not allowed to do that!'); redirect('dashboard'); }
        }


        private function loadFromFile($filepath)
        {
                // Figure out how we should be marking QSLs confirmed via QSL

                ini_set('memory_limit', '-1');
                set_time_limit(0);

                $this->load->library('adif_parser');

                $this->adif_parser->load_from_file($filepath);

                $this->adif_parser->initialize();

                $tableheaders = "<table>";
                        $tableheaders .= "<tr class=\"titles\">";
                                $tableheaders .= "<td>QSO Date</td>";
                                $tableheaders .= "<td>Call</td>";
                                $tableheaders .= "<td>Mode</td>";
                                $tableheaders .= "<td>QSL Received</td>";
                                $tableheaders .= "<td>Date QSL Received</td>";
                                $tableheaders .= "<td>QSL Sent</td>";
                                $tableheaders .= "<td>Date QSL Sent</td>";
                                $tableheaders .= "<td>Status</td>";
                        $tableheaders .= "</tr>";

                        $table = "";
                        while($record = $this->adif_parser->get_record())
                        {

                                $time_on = date('Y-m-d', strtotime($record['qso_date'])) ." ".date('H:i', strtotime($record['time_on']));

                                $qslrdate = date('Y-m-d', strtotime($record['qslrdate'])) ." ".date('H:i', strtotime($record['qslrdate']));
                                $qslsdate = date('Y-m-d', strtotime($record['qslsdate'])) ." ".date('H:i', strtotime($record['qslsdate']));

                                if (isset($record['time_off'])) {
                                        $time_off = date('Y-m-d', strtotime($record['qso_date'])) ." ".date('H:i', strtotime($record['time_off']));
                                } else {
                                   $time_off = date('Y-m-d', strtotime($record['qso_date'])) ." ".date('H:i', strtotime($record['time_on']));
                                }


       // function qsl_update($datetime, $callsign, $band, $qslsent, $qslsvia, $qslsdate, $qslrcvd, $qslrvia, $qslrdate) {

                                $qsl_status = $this->logbook_model->qsl_update($time_on, $record['call'], $record['band'], $record['qsl_sent'], $record['qsl_sent_via'], $qslsdate, $record['qsl_rcvd'], $record['qsl_rcvd_via'], $qslrdate);

                                $table .= "<tr>";
                                        $table .= "<td>".$time_on."</td>";
                                        $table .= "<td>".$record['call']."</td>";
                                        $table .= "<td>".$record['mode']."</td>";
                                        $table .= "<td>".$record['qsl_rcvd']."</td>";
                                        $table .= "<td>".$qslrdate."</td>";
                                        $table .= "<td>".$record['qsl_sent']."</td>";
                                        $table .= "<td>".$qslsdate."</td>";
                                        $table .= "<td>".$qsl_status."</td>";
                                $table .= "</tr>";
                        }

                        if ($table != "")
                        {
                                $table .= "</table>";
                                $data['qsl_table_headers'] = $tableheaders;
                                $data['qsl_table'] = $table;
                }

                unlink($filepath);

                $data['page_title'] = "QSL ADIF Information";
                $this->load->view('layout/header', $data);
                $this->load->view('qsl/analysis');
                $this->load->view('layout/footer');



	}

	public function index()
	{
		$this->load->view('layout/header');
		$this->load->view('qsl/main');
		$this->load->view('layout/footer');
	}


 	public function import() {
                $data['page_title'] = "QSL ADIF Import";
 		$config['upload_path'] = './uploads/';
                $config['allowed_types'] = 'adi|ADI';

                $this->load->library('upload', $config);

                $this->load->model('logbook_model');





                if ($this->input->post('qslimport') == 'fetch')
                {


		}
		else
                {
                        if ( ! $this->upload->do_upload())
                        {

                                $data['error'] = $this->upload->display_errors();

                                $this->load->view('layout/header', $data);
                                $this->load->view('qsl/import');
                                $this->load->view('layout/footer');
                        }
                        else
                        {
                                $data = array('upload_data' => $this->upload->data());

                                $this->loadFromFile('./uploads/'.$data['upload_data']['file_name']);
                        }
                }


	}

}
