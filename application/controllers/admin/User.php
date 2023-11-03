<?php


defined('BASEPATH') or exit('No direct script access allowed');

class User extends CI_Controller
{

	public $form_validation;
	public $session;
	public $input;
	public $db;

	public function __construct()
	{
		parent::__construct();
		access_level_admin();
		$this->load->model('UserModel');
	}

	public function index()
	{
		$page_data['page_title'] = 'Manage User';
		$page_data['page_name'] = 'admin/user/user-add';
		return $this->load->view('admin/common', $page_data);
	}

	public function add()
	{
		//user data
		$this->form_validation->set_rules('name', 'Full Name ', 'trim|required');
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|is_unique[user.email]');
		$this->form_validation->set_rules('mobile', 'Mobile', 'trim|required|numeric|exact_length[10]|is_unique[user.mobile]');
		$this->form_validation->set_rules('password', 'Password', 'trim|required');
		$this->form_validation->set_rules('user_type', 'User Type', 'trim|required|in_list[CUSTOMER,VENDOR,WORKER]');
		$this->form_validation->set_rules('profile', 'Profile', 'trim|mime_in[profile,image/jpg,image/jpeg,image/png,]');

		if ($this->form_validation->run() == false) {
			$r['success'] = 0;
			$r['message'] = validation_errors();
		} else {
			$data = $this->input->post();
			$user = [];

			$user['name'] = $data['name'];
			$user['user_id'] = $this->session->userdata('login')['user_id'];
			$user['email'] = $data['email'];
			$user['mobile'] = $data['mobile'];
			$user['password'] = sha1($data['password']);
			$user['type'] = $data['user_type'];

			// upload image    first fileName and second pathName
			$profile = imageUpload('profile', 'profile');
			if (!empty($profile)) {
				$user['profile'] = $profile['file_name'];
			}

			$insert = $this->db->insert('user', $user);
			if (isset($insert)) {
				$r['success'] = 1;
				$r['message'] = "User Add SuccessFully.";
			} else {
				$r['success'] = 0;
				$r['message'] = "User Add Failed, Please Try again.";
			}
		}
		$this->session->set_flashdata('flash', $r);
		redirect(base_url('admin/user/report'), 'refresh');
	}

	public function report()
	{
		$page_data['page_title'] = 'User Report';
		$page_data['page_name'] = 'admin/user/user-report';
		return $this->load->view('admin/common', $page_data);
	}

	public function getUserList()
	{
		$postData = $this->input->post();
		$data = $this->UserModel->getUsers($postData);
		echo json_encode($data);
	}

	public function edit($update_id)
	{
		try {
			$id = decrypt_id($update_id);
			$user_id = $this->session->userdata('login')['user_id'];
			$page_data['data'] = $this->db->select('id,name,type,email,mobile,profile')->get_where('user', array('id' => $id, 'user_id' => $user_id))->row_array();
			$page_data['id'] = $update_id;
			$page_data['page_title'] = 'Manage User';
			$page_data['page_name'] = 'admin/user/user-add';
			return $this->load->view('admin/common', $page_data);
		} catch (\Throwable | \ErrorException | \Error | \Exception $e) {
			$r['success'] = 0;
			$r['message'] = $e->getMessage();
			$this->session->set_flashdata('flash', $r);
			redirect(base_url('admin/user/report'), 'refresh');
		}
	}

	public function update()
	{
		$data = $this->input->post();
		$id = decrypt_id($data['id']);

		$this->form_validation->set_rules('name', 'Full Name ', 'trim|required');
		$this->form_validation->set_rules('id', 'Update Id', 'trim|required');
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
		$this->form_validation->set_rules('mobile', 'Mobile', 'trim|required|numeric|exact_length[10]');
		$this->form_validation->set_rules('user_type', 'User Type', 'trim|required|in_list[CUSTOMER,VENDOR,WORKER]');
		$this->form_validation->set_rules('profile', 'Profile', 'trim|mime_in[profile,image/jpg,image/jpeg,image/png,]');

		if ($this->form_validation->run() == false) {
			$r['success'] = 0;
			$r['message'] = validation_errors();
		} else {
			$user = [];
			$user_id = $this->session->userdata('login')['user_id'];

			$user['name'] = $data['name'];
			$user['email'] = $data['email'];
			$user['type'] = $data['user_type'];
			$allData = $this->db->select('mobile,email,profile')->get_where('user', array('id' => $id, 'user_id' => $user_id));
			$fetch_data = $allData->row_array();

			// Mobile unique
			if ($fetch_data['mobile'] == $data['mobile']) {
				$user['mobile'] = $data['mobile'];
			} else {
				$num_rows = $allData->num_rows();
				if ($num_rows == 0) {
					$user['mobile'] = $data['mobile'];
				} else {
					$r['message'] = "Mobile No Already Exists.";
					$r['success'] = 0;
					$this->session->set_flashdata('flash', $r);
					redirect(base_url('admin/user/report'), 'refresh');
				}
			}

			// Email unique
			if ($fetch_data['email'] == $data['email']) {
				$user['email'] = $data['email'];
			} else {
				$num_rows = $allData->num_rows();
				if ($num_rows == 0) {
					$user['email'] = $data['email'];
				} else {
					$r['message'] = "Email Id Already Exists.";
					$r['success'] = 0;
					$this->session->set_flashdata('flash', $r);
					redirect(base_url('admin/user/report'), 'refresh');
				}
			}

			// upload image    first fileName and second pathName
			if ($this->input->post() && !empty($_FILES['profile']['name'])) {
				if (!empty($fetch_data['profile'])) {
					if (file_exists('assets/uploads/profile' . $fetch_data['profile'])) {
						@unlink('assets/uploads/profile' . $fetch_data['profile']);
					}
				}
				$profileData = imageUpload('profile', 'profile');
				if (!empty($profileData)) {
					$user['profile'] = $profileData['file_name'];
				}
			}

			$user = $this->db->where('id', $id)->where('user_id', $user_id)->update('user', $user);
			if (isset($user)) {
				$r['success'] = 1;
				$r['message'] = "User Updated SuccessFully.";
			} else {
				$r['success'] = 0;
				$r['message'] = "User Updated Failed, Please Try again.";
			}
		}
		$this->session->set_flashdata('flash', $r);
		redirect(base_url('admin/user/report'), 'refresh');
	}

	public function status()
	{
		try {
			$this->form_validation->set_rules('status', 'Status', 'trim|required');
			$this->form_validation->set_rules('id', 'Update Id', 'trim|required');

			if ($this->form_validation->run() == false) {
				$r['success'] = 0;
				$r['message'] = validation_errors();
			} else {
				$data = $this->input->post();
				$user_id = $this->session->userdata('login')['user_id'];
				$id = decrypt_id($data['id']);

				$response = $this->db->where(array('id' => $id, 'user_id' => $user_id))->update('user', ['status' => $data['status']]);

				if (isset($response)) {
					echo json_encode(['success' => true, 'message' => 'Status Updated successfully.']);
				} else {
					echo json_encode(['success' => false, 'error' => json_encode(validation_errors())]);
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
	}
}
