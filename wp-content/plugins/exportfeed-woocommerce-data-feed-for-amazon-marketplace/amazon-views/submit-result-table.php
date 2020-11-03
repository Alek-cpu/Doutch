<?php if ( array_key_exists('Status', $cpf_data) && $cpf_data['Status']) :  ?>
    <table style="text-align:left">
        <tr>
            <th>Submission ID</th>
            <td>: <?= $cpf_data['FeedSubmissionId'] ?></td>
        </tr>
        <tr>
            <th>Current Status</th>
            <td>: <?= $cpf_data['FeedProcessingStatus'] ?></td>
        </tr>
        <tr>
            <th>Type</th>
            <td>: <?= $cpf_data['type'] ?></td>
        </tr>
        <tr>
            <th>Feed Title</th>
            <td>: <?= $cpf_data['feed_title'] ?></td>
        </tr>
        <tr>
            <th></th>
            <td>We are all done here. Now click on <strong>Reports</strong> button below and see the results and status updates of this feed.</td>
        </tr>
    </table>
<?php else : ?>
    <p>
        <?php
            echo '<strong style="color:red";>*'. $cpf_result->ErrorMessage.'</strong> Please Submit once again correcting the error.'; ?>
    </p>
<?php endif; ?>

