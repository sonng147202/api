<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Library\Email\Smtp2GoMail;


class MailQueue extends Model {
    protected $table = 'mail_queues';

    protected $fillable = [
        'notification_id',
        'subject',
        'attach_file',
        'content',
        'group_id',
        'user_id',
        'cc',
        'req',
        'res',
        'sended',
        'success',
        'flag'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public static function getMailTemplate($templete, $variable) {
        return view($templete, $variable)->render();
    }

    public static function saveMailToQueue($params) {
        $html_body = self::getMailTemplate($params['templete'], $params['variable']);
        $model = new MailQueue();
        $model->subject = $params['subject'];
        $model->content = $html_body;
        $model->group_id = 3;
        $model->user_id = $params['user_id'];
        $model->sended = 0;
        $model->success = 0;
        $model->flag = 0;
        $model->save();
        return $model;
    }

    public static function SendMailNow($params){
        $smtp = new Smtp2GoMail();
        $html_body = self::getMailTemplate($params['templete'], $params['variable']);
        $sender = env('SENDER_MAIL');
        $cc = [];
        $data = $smtp->sendMailApi($params['to'], $sender, $params['subject'], $html_body, $cc);
    }


}