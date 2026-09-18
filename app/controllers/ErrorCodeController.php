<?php

class ErrorCodeController extends BaseController
{
    private ErrorCode $errorCode;

    public function __construct()
    {
        $this->checkAuth();
        $this->errorCode = new ErrorCode();
    }

    public function index(): void
    {
        $id = (int) base64_decode((string) ($_GET['id'] ?? ''));
        $error_code = $id > 0 ? new ErrorCode($id) : new ErrorCode();
        $this->view('error_code', ['error_code' => $error_code]);
    }

    public function save(): void
    {
        if (!$this->isPost()) {
            $this->redirect('error_code_list');
        }

        $data = $this->post();
        $id = !empty($data['id']) ? (int) $data['id'] : 0;
        $code = trim((string) ($data['f1'] ?? ''));
        $message = trim((string) ($data['f2'] ?? ''));

        if ($code === '' || $message === '') {
            $this->setFlash('danger', 'Error code and message are required.');
            $this->redirect($id > 0 ? 'error_code?id=' . base64_encode($id) : 'error_code');
        }

        $saveData = [
            'code' => (int) $code,
            'message' => $message,
        ];

        if ($id > 0) {
            $this->errorCode->update($id, $saveData);
            $this->setFlash('success', 'Error code updated successfully.');
        } else {
            $this->errorCode->insert($saveData);
            $this->setFlash('success', 'Error code created successfully.');
        }

        $this->redirect('error_code_list');
    }

    public function delete($id): void
    {
        $decodedId = (int) base64_decode((string) $id);
        if ($decodedId > 0) {
            $this->errorCode->delete($decodedId);
            $this->setFlash('success', 'Error code deleted successfully.');
        }

        $this->redirect($this->get('redirect', 'error_code_list'));
    }
}
