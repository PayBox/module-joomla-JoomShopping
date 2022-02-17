<div class="col100">
    <fieldset class="adminform">
        <table class="admintable" width = "100%" >
            <tr>
                <td  class="key">
                    <?php echo _JSHOP_paybox_ENABLE_OFD;?>
                </td>
                <td>
                    <input type = "checkbox" id="enable_ofd"  class = "checkbox" name = "pm_params[enable_ofd]"   <?php if ($params['enable_ofd'] === "on") { echo "checked"; } ?> />
                    <?php
                    echo " ".JHTML::tooltip(_JSHOP_paybox_ENABLE_OFD);
                    ?>
                </td>
            </tr>
            <tr>
                <td  class="key">
                    <?php echo _JSHOP_paybox_TAX_TYPE;?>
                </td>
                <td>
                    <input type = "text" id="taxtype" <?php if (!$params['enable_ofd']) { echo "disabled"; } ?> class = "inputbox" name = "pm_params[tax_type]" size="5" value = "<?php echo $params['tax_type']?>" />
                    <?php echo JHTML::tooltip(_JSHOP_paybox_TAX_TYPE);?>
                </td>
            </tr>
            <tr>
                <td style="width:250px;" class="key">
                    <?php echo _JSHOP_TESTMODE;?>
                </td>
                <td>
                    <?php
                    print JHTML::_('select.booleanlist', 'pm_params[test_mode]', 'class = "inputbox" size = "1"', $params['test_mode']);
                    echo " ".JHTML::tooltip(_JSHOP_paybox_TEST_MODE_DESCRIPTION);
                    ?>
                </td>
            </tr>
            <tr>
                <td  class="key">
                    <?php echo _JSHOP_paybox_MERCHANT_ID;?>
                </td>
                <td>
                    <input type = "text" class = "inputbox" name = "pm_params[merchant_id]" size="5" value = "<?php echo $params['merchant_id']?>" />
                    <?php echo JHTML::tooltip(_JSHOP_paybox_MERCHANT_ID_DESCRIPTION);?>
                </td>
            </tr>
            <tr>
                <td  class="key">
                    <?php echo _JSHOP_paybox_SECRET_KEY;?>
                </td>
                <td>
                    <input type = "text" class = "inputbox" name = "pm_params[secret_key]" size="30" value = "<?php echo $params['secret_key']?>" />
                    <?php echo JHTML::tooltip(_JSHOP_paybox_SECRET_KEY_DESCRIPTION);?>
                </td>
            </tr>
            <tr>
                <td  class="key">
                    <?php echo _JSHOP_paybox_LIFETIME;?>
                </td>
                <td>
                    <input type = "text" class = "inputbox" name = "pm_params[lifetime]" size="30" value = "<?php echo $params['lifetime']?>" />
                    <?php echo JHTML::tooltip(_JSHOP_paybox_LIFETIME_DESCRIPTION);?>
                </td>
            </tr>
            <tr>
                <td class="key">
                    <?php echo _JSHOP_TRANSACTION_END;?>
                </td>
                <td>
                    <?php
                    print JHTML::_('select.genericlist', $orders->getAllOrderStatus(), 'pm_params[transaction_end_status]', 'class = "inputbox" size = "1"', 'status_id', 'name', $params['transaction_end_status'] );
                    echo " ".JHTML::tooltip(_JSHOP_paybox_TRANSACTION_END_DESCRIPTION);
                    ?>
                </td>
            </tr>
            <tr>
                <td class="key">
                    <?php echo _JSHOP_TRANSACTION_PENDING;?>
                </td>
                <td>
                    <?php
                    echo JHTML::_('select.genericlist',$orders->getAllOrderStatus(), 'pm_params[transaction_pending_status]', 'class = "inputbox" size = "1"', 'status_id', 'name', $params['transaction_pending_status']);
                    echo " ".JHTML::tooltip(_JSHOP_paybox_TRANSACTION_PENDING_DESCRIPTION);
                    ?>
                </td>
            </tr>
            <tr>
                <td class="key">
                    <?php echo _JSHOP_TRANSACTION_FAILED;?>
                </td>
                <td>
                    <?php
                    echo JHTML::_('select.genericlist',$orders->getAllOrderStatus(), 'pm_params[transaction_failed_status]', 'class = "inputbox" size = "1"', 'status_id', 'name', $params['transaction_failed_status']);
                    echo " ".JHTML::tooltip(_JSHOP_paybox_TRANSACTION_FAILED_DESCRIPTION);
                    ?>
                </td>
            </tr>
        </table>
    </fieldset>
    <div class="clr"></div>


    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
    <script type="text/javascript">
        $(function () {
            $("#enable_ofd").click(function () {
                if ($(this).is(":checked")) {
                    $("#taxtype").removeAttr("disabled");
                    $("#taxtype").focus();
                } else {
                    $("#taxtype").attr("disabled", "disabled");
                }
            });
        });
    </script>
