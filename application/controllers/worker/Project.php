<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Project extends CI_Controller
{

	public $form_validation;
	public $session;
	public $input;
	public $db;

	public function __construct()
	{
		parent::__construct();
		access_level('WORKER');
		$this->load->model('worker/ProjectModel');
	}

<<<<<<< HEAD

=======
>>>>>>> 954640e6a85ba5d8447ec5666f457fb16d4e6b05
	public function report()
	{
		$page_data['page_title'] = 'Project Report';
		$user_id = $this->session->userdata('login')['user_id'];
		$page_data['city'] = $this->db->select('id,name')->get_where('city', array('status' => 'ACTIVE'))->result_array();
		$page_data['worker'] = $this->db->select('id,name')->get_where('user', array('type' => 'WORKER', 'user_id' => $user_id, 'status' => 'ACTIVE'))->result_array();
		$page_data['vendor'] = $this->db->select('id,name')->get_where('user', array('type' => 'VENDOR', 'user_id' => $user_id, 'status' => 'ACTIVE'))->result_array();
		$page_data['customer'] = $this->db->select('id,name')->get_where('user', array('type' => 'CUSTOMER', 'user_id' => $user_id, 'status' => 'ACTIVE'))->result_array();
		$page_data['page_name'] = 'worker/project_report';
		return $this->load->view('worker/common', $page_data);
	}

	public function getProjectList()
	{
		$postData = $this->input->post();
		$data = $this->ProjectModel->getProjectReport($postData);
		echo json_encode($data);
	}

<<<<<<< HEAD
	public function status()
	{
		try {
			$this->form_validation->set_rules('status', 'Status', 'trim|required');
			$this->form_validation->set_rules('id', 'Update Id', 'trim|required');
			$this->form_validation->set_rules('type', 'Status type', 'trim|required');

			if ($this->form_validation->run() == false) {
				$r['success'] = 0;
				$r['message'] = validation_errors();
			} else {
				$data = $this->input->post();
				$user_id = $this->session->userdata('login')['user_id'];
				$id = $data['id'];

				if ($data['type'] == 'status') {
					$response = $this->db->where(array('id' => $id, 'user_id' => $user_id))->update('project', ['status' => $data['status']]);
					if (isset($response)) {
						echo json_encode(['success' => true, 'message' => 'Status Updated successfully.']);
					} else {
						echo json_encode(['success' => false, 'error' => json_encode(validation_errors())]);
					}
				} elseif ($data['type'] == 'project_status') {
					$response = $this->db->where(array('id' => $id, 'user_id' => $user_id))->update('project', ['project_status' => $data['status']]);
					if (isset($response)) {
						echo json_encode(['success' => true, 'message' => 'Status Updated successfully.']);
					} else {
						echo json_encode(['success' => false, 'error' => json_encode(validation_errors())]);
					}
				}
			}
		} catch (\Throwable | \ErrorException | \Error | \Exception $e) {
			$message = [
				'success' => false,
				'error' => $e->getMessage(),
				'data' => []
			];
			echo json_encode($message);
		}
=======
	public function update_status()
	{
		$this->form_validation->set_rules('id', 'Update Id', 'trim|required');
		$this->form_validation->set_rules('status', 'Status', 'trim|required');

		if ($this->form_validation->run() == false) {
			$r['success'] = 0;
			$r['message'] = validation_errors();
		} else {
			$data = $this->input->post();

			$user_id = $this->session->userdata('login')['user_id'];
			$user_type = $this->session->userdata('login')['user_type'];
			$id = $data['id'];
			$insert_data = [];

			$arraydata = [];
			$files = $_FILES;
			$images = $_FILES['work_image']['name'];

			$files = $_FILES;
			$cpt = count($_FILES['work_image']['name']);
			for ($i = 0; $i < $cpt; $i++) {
				$_FILES['work_image']['name'] = $files['work_image']['name'][$i];
				$_FILES['work_image']['type'] = $files['work_image']['type'][$i];
				$_FILES['work_image']['tmp_name'] = $files['work_image']['tmp_name'][$i];
				$_FILES['work_image']['error'] = $files['work_image']['error'][$i];
				$_FILES['work_image']['size'] = $files['work_image']['size'][$i];

				$config = array();
				$config['upload_path'] = 'assets/uploads/work_image';
				$config['allowed_types'] = 'gif|jpg|png';
				// $config['max_size']      = '50000';
				$config['encrypt_name'] = TRUE;
				$config['overwrite']     = FALSE;
				$this->upload->initialize($config);

				if (!empty($this->upload->do_upload('work_image'))) {
					$upload_data = $this->upload->data();
					resize_image($upload_data['file_name'],'assets/uploads/work_image');

					// // Configure image manipulation settings
					// $config['image_library'] = 'gd2'; // Choose the image library (gd, gd2, imagemagick, netpbm, etc)
					// $config['source_image'] = $upload_data['full_path']; // Path to the uploaded image
					// $config['maintain_ratio'] = TRUE; // Maintain the original aspect ratio
					// $config['width'] = 800; // New image width
					// $config['height'] = 600; // New image height
					// $config['quality'] = '20%'; // New image quality

					// // $this->load->library('image_lib', $config);

					// $this->image_lib->clear();
					// $this->image_lib->initialize($resize);

					// if (!empty($this->image_lib->resize())) {
						$insert_data['name'] =  $upload_data['file_name'];
						$insert_data['project_detail_id'] = $id;
						$insert_data['type'] = $data['status'];
						// $this->db->insert('project_detail_image', $insert_data);
					// }
				}
			}
			$update_data = [];
			$update_data['worker_status'] = $data['status'];
			$update_data['worker_remark'] = $data['remark'];

			$response = $this->db->where(array('id' => $id, 'worker_id' => $user_id))->update('project_detail', $update_data);

			if (isset($response)) {
				$r['success'] = 1;
				$r['message'] = "Your Status Updated successfully.";
			} else {
				$r['success'] = 0;
				$r['message'] = "Your Status Updated Failed.";
			}
		}
		$this->session->set_flashdata('flash', $r);
		redirect(base_url('worker/project/report'), 'refresh');
	}

	public function viewImage()
	{
		$project_detail_id = $this->input->post('project_detail_id');
		$page_data['INPROCESS'] = $this->db->select('id,name')->get_where('project_detail_image', array('project_detail_id' => $project_detail_id, 'type' => 'INPROCESS'))->result_array();
		$page_data['COMPLATED'] = $this->db->select('id,name')->get_where('project_detail_image', array('project_detail_id' => $project_detail_id, 'type' => 'COMPLATED'))->result_array();
		$res = $this->load->view('worker/view_image.php', $page_data);
		echo json_encode($res);
>>>>>>> 954640e6a85ba5d8447ec5666f457fb16d4e6b05
	}
}
