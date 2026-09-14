<?php

class CompanyController extends BaseController
{
    private Company $company;

    public function __construct()
    {
        $this->checkAuth();
        $this->company = new Company();
    }

    public function index(): void
    {
        $id = (int) base64_decode((string) ($this->get('id', '')));
        $company = $id > 0 ? new Company($id) : new Company();

        if ($id > 0 && empty($company->row)) {
            $this->setFlash('danger', 'Company not found.');
            $this->redirect('company_list');
        }

        $this->view('company', ['company' => $company]);
    }

    public function save(): void
    {
        if (!$this->isPost()) {
            $this->redirect('company_list');
        }

        $id = (int) $this->post('id', 0);
        $name = trim((string) $this->post('f1', ''));
        $website = trim((string) $this->post('f2', ''));
        $description = trim((string) $this->post('f3', ''));

        if ($name === '' || strlen($name) < 2 || strlen($name) > 100) {
            $this->setFlash('danger', 'Company name must be between 2 and 100 characters.');
            $this->redirect($id > 0 ? 'company?id=' . base64_encode($id) : 'company');
        }

        if ($website !== '' && !filter_var($website, FILTER_VALIDATE_URL)) {
            $this->setFlash('danger', 'Please enter a valid website URL.');
            $this->redirect($id > 0 ? 'company?id=' . base64_encode($id) : 'company');
        }

        $data = [
            'f1' => $name,
            'f2' => $website !== '' ? $website : null,
            'f3' => $description !== '' ? $description : null,
            'status' => 1,
        ];

        $existing = $id > 0 ? new Company($id) : null;
        $oldImage = $existing && is_object($existing->row) ? $existing->row->img1 : null;
        $image = $this->uploadLogo();
        if ($image !== null) {
            $data['img1'] = $image;
        }

        try {
            if ($id > 0) {
                $success = $this->company->update($id, $data);
                if ($success && $image !== null) {
                    $this->removeLogo($oldImage);
                }
            } else {
                $success = $this->company->insert($data) > 0;
            }

            $this->setFlash(
                $success ? 'success' : 'danger',
                $success ? ($id > 0 ? 'Company updated successfully.' : 'Company created successfully.') : 'Unable to save company.'
            );
            $this->redirect($success && $id > 0 ? 'company?id=' . base64_encode($id) : 'company_list');
        } catch (Throwable $exception) {
            if ($image !== null) {
                $this->removeLogo($image);
            }
            error_log('Company save error: ' . $exception->getMessage());
            $this->setFlash('danger', 'Unable to save company.');
            $this->redirect($id > 0 ? 'company?id=' . base64_encode($id) : 'company');
        }
    }

    public function delete($id = 0): void
    {
        $id = (int) $id;
        $company = $id > 0 ? new Company($id) : null;
        $image = $company && is_object($company->row) ? $company->row->img1 : null;
        $success = $id > 0 && $this->company->delete($id);

        if ($success) {
            $this->removeLogo($image);
        }

        $this->setFlash($success ? 'success' : 'danger', $success ? 'Company deleted successfully.' : 'Unable to delete company.');
        $this->redirect('company_list');
    }

    private function uploadLogo(): ?string
    {
        if (empty($_FILES['img1']) || $_FILES['img1']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $file = $_FILES['img1'];
        if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 5 * 1024 * 1024) {
            return null;
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            return null;
        }

        $directory = ROOT . '/uploads/companies';
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = bin2hex(random_bytes(8)) . '.' . $extension;
        if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $filename)) {
            return null;
        }

        return 'uploads/companies/' . $filename;
    }

    private function removeLogo(?string $path): void
    {
        if ($path && strpos($path, 'uploads/companies/') === 0) {
            @unlink(ROOT . '/' . $path);
        }
    }
}
