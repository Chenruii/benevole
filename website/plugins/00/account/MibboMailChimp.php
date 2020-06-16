<?php


// base sur https://github.com/drewm/mailchimp-api
require_once MIB_ROOT . 'feeds/mailchimp/src/MailChimp.php';

use \DrewM\MailChimp\MailChimp;

class MibboMailChimp
{

    /** @var MailChimp $mailChimp */
    protected $mailChimp;
    /** @var BlogPlugin $blog */
    protected $blog;


    public function getConfig()
    {

//        return [
//            'list_id' => 'b534ba9493',
//            'folder' => '83fcd947b2',
//            'templateFolder' => '112f4c7d2d',
//            'segment' => 100413,
//            'apiKey' => '7c90dbd40cd5d11e7e5bc369d1007559-us20',
//            'from_name' => 'aude.blancbrude@2boandco.com',
//            'reply_to' => 'aude.blancbrude@2boandco.com',
//        ];
        // test
        return [
            'list_id' => '14487da672',
            'folder' => 'f77a04ac3a',
            'templateFolder' => 'f06a1f77a1',
            'segment' => 52641,
            'apiKey' => 'f2501adc14864d2f7ba6d739bbe6db01-us20',
            'from_name' => '2boandco.com',
            'reply_to' => 'support@2boandco.com',
        ];

    }

    public function init()
    {
        $config = $this->getConfig();
        // var_dump($config);

        if (!$this->mailChimp)
            $this->mailChimp = new MailChimp($config['apiKey']);

        $this->MailingListInfo = [
            "list_id" => $config['list_id'],
            'newsletter' => [
                'folder' => $config['folder'],
                'templateFolder' => $config['templateFolder'],
                'segment' => $config['segment']
            ]];

        $this->mailSetting = [
            'from_name' => $config['from_name'],
            'reply_to' => $config['reply_to'],
        ];


        if (!$this->blog)
            $this->blog = BlogPlugin::getInstance();
    }

    public function test()
    {
        echo '<pre>';
        $this->init();
        //$result = $this->mailChimp->get('lists');
       // var_dump($result);
//        $result = $this->mailChimp->get('campaign-folders');
//        var_dump($result);
//        $result = $this->mailChimp->get('template-folders');
//        var_dump($result);
        $result = $this->mailChimp->get("/lists/{$this->MailingListInfo['list_id']}/segments");
        print_r($result);

        $result = $this->mailChimp->get("/lists/{$this->MailingListInfo['list_id']}/segments/{$this->MailingListInfo['newsletter']['segment']}");
//        echo '<pre>';
        print_r($result);
        $result = $this->mailChimp->get("/lists/{$this->MailingListInfo['list_id']}/segments/{$this->MailingListInfo['newsletter']['segment']}/members");
//        echo '<pre>';
        print_r($result);


       echo '</pre>';

       //https://us20.api.mailchimp.com/3.0/lists/b534ba9493/segments/100409/members
//        https://us20.api.mailchimp.com/3.0/lists/b534ba9493/segments/100409
    }

    public function createNewLetter($articlesCount)
    {
        $this->init();
        $articlesCount = min($articlesCount, 5);
        $listManager = $this->blog->GetList();
        $state = ['sort_by' => 'date', 'sort_dir' => 'desc', 'take' => $articlesCount];
        $filters = [];
        $this->blog->modifyFilters($filters); // on n'envoie pas les non publiés
        $listManager->loadDataByFilter($state, $filters);
        return $this->createAndSendCampaign($listManager, 'newsletter');
    }


    protected function createAndSendCampaign(MibboList $list, $type)
    {
        $template = $this->createTemplate($list, $type);
        if (empty($template['id'])) {
            return false;
        }
        $campaign = $this->createCampaign($template['id'], $type);
        if (empty($campaign['id'])) {
            return false;
        }

//        $ok = $this->sendCampaign($campaign['id']);
////        return $ok;
///

        return true ;
    }

    protected function createTemplate(MibboList $list, $type)
    {
        //Enter a value less than 50 characters long
        $datas = [
            'name' => 'Gazette St-Michel (' . date('d/m/Y') . ')',
            'html' => $this->createHtmlForNewsLetter($list),
            'folder' => $this->MailingListInfo[$type]['templateFolder']
        ];
        $result = $this->mailChimp->post('templates', $datas);
        return $result;
    }

    protected function sendCampaign($campaignId)
    {
        $result = $this->mailChimp->post("/campaigns/{$campaignId}/actions/send");
        return $result === true;

    }

    protected function createCampaign($templateId, $type)
    {
        $datas = [
            'type' => 'regular',
            'recipients' => [
                'list_id' => $this->MailingListInfo['list_id']
            ],
            'settings' => [
                'subject_line' => 'la liste des derniers articles de la gazette St-Michel',
                'preview_text' => 'la liste des derniers articles de la gazette St-Michel',
                'title' => 'la liste des derniers articles de la gazette St-Michel (' . date('d/m/Y') . ')',
                'from_name' => $this->mailSetting['from_name'],
                'reply_to' => $this->mailSetting['reply_to'],
                'folder_id' => $this->MailingListInfo[$type]['folder'],
                'template_id' => $templateId
            ]
        ];

        if (!empty($this->MailingListInfo[$type]['segment'])) {
            $datas['recipients']['segment_opts'] = [
                'saved_segment_id' => $this->MailingListInfo[$type]['segment']
            ];
        }

        $campaign = $this->mailChimp->post('campaigns', $datas);
        if (empty($campaign['id'])) {
            return false;
        }
        return $campaign;
    }

    public function getPreviewTemplate($articlesCount){

        $this->init();
        $articlesCount = min($articlesCount, 5);
        $listManager = $this->blog->GetList();
        $state = ['sort_by' => 'date', 'sort_dir' => 'desc', 'take' => $articlesCount];
        $filters = [];
        $this->blog->modifyFilters($filters); // on n'envoie pas les non publiés
        $listManager->loadDataByFilter($state, $filters);
        return $this->createHtmlForNewsLetter($listManager);
    }

    protected function createHtmlForNewsLetter(MibboList $list)
    {
        ob_start();
        include MIB_ACCOUNT_HTMLPARTS . "mail-newsletter.php";
        $view = ob_get_contents();
        ob_end_clean();
        return $view;
    }

    public function updateUser($email, $newsLetter)
    {
        $this->init();
        $datas = [
            'tags' => $this->getTags($email, $newsLetter, true)
        ];
        // /lists/{list_id}/members/{subscriber_hash}/tags
        $result = $this->mailChimp->post("/lists/{$this->MailingListInfo['list_id']}/members/{$this->getmemberHash($email)}/tags", $datas);
//        var_dump($result,);
//        exit();
    }

    private function getTags($email, $newsLetter, $forUpdate = false)
    {
        $tags = [];
        if ($newsLetter || strpos($email, 'stmichel.fr') !== false) {
            if ($forUpdate) {
                $tags[] = ['name' => 'Notification', 'status' => 'active'];
            } else {
                $tags[] = 'Notification';
            }
        } else {
            if ($forUpdate) {
                $tags[] = ['name' => 'Notification', 'status' => 'inactive'];
            }
        }
        return $tags;
    }

    private function getmemberHash($email)
    {
        $hash = md5(strtolower($email));
        return $hash;
    }

    public function getMember($email)
    {
        $this->init();
        $member = $this->mailChimp->get("/lists/{$this->MailingListInfo['list_id']}/members/{$this->getmemberHash($email)}");
        return $member;
    }

    public function createMember($email, $firstName, $lastName, $newsLetter)
    {
        $this->init();
        $datas = [
            'email_address' => $email,
            'status' => 'subscribed',
            "merge_fields" => [
                "FNAME" => $firstName,
                "LNAME" => $lastName
            ],
            'tags' => $this->getTags($email, $newsLetter)
        ];
        $member = $this->mailChimp->post("/lists/{$this->MailingListInfo['list_id']}/members", $datas);
        return $member;
    }

    public function deleteMember($email)
    {
        $this->init();
        $this->mailChimp->delete("/lists/{$this->MailingListInfo['list_id']}/members/{$this->getmemberHash($email)}");
    }

}