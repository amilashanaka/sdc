<?php

class HealthMonitorController extends BaseController
{
    /** @var Health */
    private $health;

    public function __construct()
    {
        $this->checkAuth();
        $this->health = new Health();
    }

    public function index(): void
    {
        $user = $this->getCurrentUser();

        $settings = new Setting(1);
        $deviceType = new DeviceType($settings->device_type ?? 0);

        $health_data = $this->health->get_all();
        $health_items = [];
        foreach ($health_data as $h) {
            $is_active = ($h->status == 1);
            $health_items[] = [
                'name'        => $h->f1,
                'sequence'    => $h->f2,
                'description' => $h->f3,
                'value'       => $h->f4,
                'icon'        => $h->f5,
                'color'       => $h->f6,
                'state'       => $is_active ? 'running' : 'idle',
                'label'       => $is_active ? 'OK' : 'Idle',
            ];
        }

        usort($health_items, function ($a, $b) {
            return ($a['sequence'] ?? 0) <=> ($b['sequence'] ?? 0);
        });

        $module_model = new Module();
        $all_modules = $module_model->get_all();
        $modules = [];
        foreach ($all_modules as $m) {
            $is_active = ($m->status == 1);
            $modules[] = [
                'name'        => $m->f1,
                'sequence'    => $m->f2,
                'description' => $m->f3,
                'address'     => $m->f5,
                'icon'        => $m->f6,
                'state'       => $is_active ? 'running' : 'idle',
                'label'       => $is_active ? 'Active' : 'Inactive',
            ];
        }

        usort($modules, function ($a, $b) {
            return ($a['sequence'] ?? 0) <=> ($b['sequence'] ?? 0);
        });

        $channel_model = new Channel();
        $all_channels = $channel_model->get_all();
        $channels = [];
        $active_channels = 0;
        foreach ($all_channels as $c) {
            $is_active = ($c->status == 1);
            if ($is_active) {
                $active_channels++;
            }
            $channels[] = [
                'name'        => $c->f1,
                'sequence'    => $c->f2,
                'decimation'  => $c->f3,
                'sample_rate' => $c->f4,
                'state'       => $is_active ? 'running' : 'idle',
                'label'       => $is_active ? 'Active' : 'Inactive',
            ];
        }

        usort($channels, function ($a, $b) {
            return ($a['sequence'] ?? 0) <=> ($b['sequence'] ?? 0);
        });

        $log_model = new Log();
        $recent_logs = $log_model->get_all(1);
        $logs = [];
        $error_count = 0;
        $warning_count = 0;
        foreach (array_slice($recent_logs, 0, 10) as $l) {
            $priority = (int) $l->f1;
            $severity = 'info';
            $severity_label = 'Info';
            if ($priority == 1) {
                $severity = 'warning';
                $severity_label = 'Warning';
                $warning_count++;
            } elseif ($priority == 2) {
                $severity = 'error';
                $severity_label = 'Error';
                $error_count++;
            }
            $logs[] = [
                'message'    => $l->f2,
                'priority'   => $priority,
                'severity'   => $severity,
                'label'      => $severity_label,
                'module'     => $l->module_name ?? '',
                'date'       => $l->created_date ?? '',
            ];
        }

        $flags_model = new Flag();
        $flags_row = $flags_model->find_by_id(1);

        $overall_healthy = ($error_count === 0 && $warning_count <= 2);
        $overall_status = $overall_healthy ? 'running' : 'idle';
        $overall_label = $overall_healthy ? 'Healthy' : 'Issues Detected';
        $overall_icon = $overall_healthy ? 'fa-circle-check' : 'fa-circle-xmark';

        $this->view('health_monitor', [
            'user'           => $user,
            'title'          => 'Health Monitor',
            'health_items'   => $health_items,
            'settings'       => $settings,
            'deviceType'     => $deviceType,
            'modules'        => $modules,
            'channels'       => $channels,
            'active_channel_count' => $active_channels,
            'logs'           => $logs,
            'error_count'    => $error_count,
            'warning_count'  => $warning_count,
            'flags'          => $flags_row,
            'overall_status' => $overall_status,
            'overall_label'  => $overall_label,
            'overall_icon'   => $overall_icon,
        ]);
    }
}
