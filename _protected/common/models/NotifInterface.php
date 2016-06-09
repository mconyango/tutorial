<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/06
 * Time: 5:21 PM
 */

namespace common\models;


interface NotifInterface
{
    public function updateNotification();

    /**
     * Process template for internal notification message
     * Replace the placeholders with actual data
     * @param string $template
     * @param string $item_id
     * @param string $notif_type_id
     *
     * @return array
     */
    public function processTemplate($template, $item_id, $notif_type_id);
}