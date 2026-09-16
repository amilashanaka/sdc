<?php

class ChannelListController extends BaseController
{
    public function __construct()
    {
        $this->checkAuth();
    }

    public function index(): void
    {
        $this->view('channel_list');
    }
}