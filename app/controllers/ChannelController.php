<?php

class ChannelController extends BaseController
{
    private Channel $channel;

    public function __construct()
    {
        $this->checkAuth();
        $this->channel = new Channel();
    }

    public function index(): void
    {
        $id = (int) base64_decode((string) ($_GET['id'] ?? ''));
        $channel = $id > 0 ? new Channel($id) : new Channel();
        $this->view('channel', ['channel' => $channel]);
    }

    public function activate($id): void
    {
        $decodedId = (int) base64_decode((string) $id);
        if ($decodedId > 0) {
            $this->channel->update($decodedId, ['status' => 1]);
            $this->setFlash('success', 'Channel activated successfully');
        }
        $redirect = $this->get('redirect', 'channel_list');
        $this->redirect($redirect);
    }

    public function deactivate($id): void
    {
        $decodedId = (int) base64_decode((string) $id);
        if ($decodedId > 0) {
            $this->channel->update($decodedId, ['status' => 0]);
            $this->setFlash('success', 'Channel deactivated successfully');
        }
        $redirect = $this->get('redirect', 'channel_list');
        $this->redirect($redirect);
    }

    public function save(): void
    {
        if (!$this->isPost()) {
            $this->redirect('channel_list');
        }

        $data = $this->post();
        $id = !empty($data['id']) ? (int) $data['id'] : 0;
        $name = trim((string) ($data['f1'] ?? ''));
        $seq = (int) ($data['f2'] ?? 0);
        $decimation = (int) ($data['f3'] ?? 0);
        $sampleRate = (int) ($data['f4'] ?? 0);
        $status = (int) ($data['status'] ?? 0);

        if (empty($name)) {
            $this->setFlash('danger', 'Channel name is required.');
            $this->redirect($id > 0 ? 'channel?id=' . base64_encode($id) : 'channel');
        }

        $saveData = [
            'f1' => $name,
            'f2' => $seq,
            'f3' => $decimation,
            'f4' => $sampleRate,
            'status' => $status,
        ];

        if ($id > 0) {
            $this->channel->update($id, $saveData);
            $this->setFlash('success', 'Channel updated successfully.');
        } else {
            $this->channel->insert($saveData);
            $this->setFlash('success', 'Channel created successfully.');
        }

        $this->redirect('channel_list');
    }
}