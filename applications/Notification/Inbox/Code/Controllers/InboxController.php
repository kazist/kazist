<?php

/*
 * This file is part of Kazist Framework.
 * (c) Dedan Irungu <irungudedan@gmail.com>
 *  For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 * 
 */

/**
 * Description of InboxController
 *
 * @author sbc
 */

namespace Notification\Inbox\Code\Controllers;

defined('KAZIST') or exit('Not Kazist Framework');

use Notification\Inbox\Code\Classes\FetchMail;
use Kazist\Controller\BaseController;

class InboxController extends BaseController {

    public function fetchmailAction() {

        $fetchMail = new FetchMail();
        $fetchMail->fetchMail();
    }

}
