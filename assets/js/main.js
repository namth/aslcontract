jQuery(document).ready(function ($) {
    /* reject press enter to submit form */
    $(document).on("keypress", "form", function (event) {
        return event.keyCode != 13;
    });

    // addnew_template.php, setup next step jquery when click on next button (.tab-pane a.btn)
    $('.tab-pane a.btn').click(function (e) {
        e.preventDefault();
        // get href value and put to var tabID
        var tabID = $(this).attr('href');
        // remove active class from current tab
        $('.nav-link').removeClass('active');
        $('.tab-pane').removeClass('show active');

        // add active class to next tab with tabID
        $('.nav-link[href="' + tabID + '"]').addClass('active');
        $(tabID).addClass('show active');

    });

    /* 
    * File: addnew_template.php
    * Process when click on "#add_datasource" button
    * 1. Hide the button
    * 2. Show the div with id "list_datasource" set to display: flex
    */
    $('#add_datasource').click(function (e) {
        e.preventDefault();
        $(this).hide();
        $('#list_datasource').css('display', 'flex');
    });

    /* 
    * File: addnew_template.php
    * Process when choose a datasource from the list, datasource have class "child_datasource"
    * 1. Get the value of the selected datasource by data-childid
    * 2. Hide the list of datasource
    * 3. Show the button with id "add_datasource"
    * 4. Call ajax to get the data of the selected datasource with childid
    */
    $('.child_datasource').click(function (e) {
        e.preventDefault();
        var childID = $(this).data('childid');
        $('#list_datasource').hide();
        $('#add_datasource').show();

        console.log(childID);
        $.ajax({
            type: 'POST',
            url: AJAX.ajax_url,
            data: {
                action: 'get_datasource',
                childID: childID
            },
            success: function (response) {
                console.log(response);

                // append the response to the div with id "replaceArea"
                $('#replaceArea').append(response);
            }
        });
    });
    
    /* 
    * File: addnew_template.php
    * Process when submit the form with id "addnew_template_form"
    */
    $('#create_document').submit(function (e) {
        e.preventDefault();
        var form = $(this);
        var formData = new FormData(form[0]);

        // get data from input hidden with name "ls_dataid", if not have value, return false
        var ls_dataid = $('input[name="ls_dataid"]').val();
        if (!ls_dataid) {
            alert('Bạn chưa chọn dữ liệu để thay thế');
            return false;
        }

        formData.append("action", "create_document");

        console.log(formData);

        $.ajax({
            type: 'POST',
            url: AJAX.ajax_url,
            data: formData,
            contentType: false,
            processData: false,
            beforeSend: function () {
                $('#create_document').addClass('hide_important');
                $('#create_loading').addClass('show_important');
            },
            success: function (response) {
                // console.log(response);
                $('#create_loading').html(response);

            }
        });
    });


    /* 
    * File: create_document.php
    * Process when click .search_data to search replace data to create document
    */
    $('.search_data').click(function (e) {
        e.preventDefault();
        var childID = $(this).data('childid');
        var search = $('input[name="datareplace_' + childID + '"]').val();
        var $this = $(this);
        
        $.ajax({
            type: 'POST',
            url: AJAX.ajax_url,
            data: {
                action: 'search_data',
                childID: childID,
                search: search
            },
            beforeSend: function () {
                $this.find('i').hide();
                $this.find('.loader').show();
                console.log(childID);
            },
            success: function (response) {
                $this.find('i').show();
                $this.find('.loader').hide();
                $('#replaceResult_' + childID).html(response);
            }
        });
    });

    /*
    * File: create_document.php
    * Process when click .select_data .nav-link to select data to replace
    */
    $(document).on('click', '.select_data .accept_select', function () {
        
        /* hide all .select_data, except parent of this */
        $('.select_data').hide();
        $(this).html('<i class="ph ph-trash fa-150p"></i>').addClass('delete_select text-danger').removeClass('accept_select');
        $(this).closest('.select_data').removeClass('select_data').show();

        var jsondata = $(this).data('asldata');
        var childid = $(this).data('childid');

        /* hide input search field with class .replace_search */
        $('#replaceSearch_' + childid).addClass('hide_div').hide();
        /* set value to input hidden name is selectdata */
        $('input[name="selectdata_' + childid + '"]').val(jsondata);
        
        var ls_dataid = $('input[name="ls_dataid"]').val();
        /* if ls_dataid has value, concatenate childid to this value seperate with ',', if has not, then set childid to that value */
        if (ls_dataid) {
            $('input[name="ls_dataid"]').val(ls_dataid + ',' + childid);
        } else {
            $('input[name="ls_dataid"]').val(childid);
        }

        /* call ajax to decrypt jsondata and show the replace field */
        $.ajax({
            type: 'POST',
            url: AJAX.ajax_url,
            data: {
                action: 'decrypt_data',
                jsondata: jsondata
            },
            success: function (response) {
                $('#replaceResult_' + childid).append(response);
            }
        });

        return false;
    });

    /* 
    * File: create_document.php
    * Process when click .choose_data .delete_select
    */
    $(document).on('click', '.delete_select', function () {
        var childid = $(this).data('childid').toString();
        var ls_dataid = $('input[name="ls_dataid"]').val();

        $(this).closest('.replace_result').html('');
        $('input[name="selectdata"]').val('');
        $('#replaceSearch_' + childid).show();

        // remove childid from ls_dataid
        var ls_dataid_arr = ls_dataid.split(',');
        var index = ls_dataid_arr.indexOf(childid);

        if (index > -1) {
            ls_dataid_arr.splice(index, 1);
        }
        // join array to string with ',', then set to input hidden name is ls_dataid
        $('input[name="ls_dataid"]').val(ls_dataid_arr.join(','));
        return false;
    });

    /* 
    * File: addnew_user.php
    * When select "Quản lý" from select with id "position", then set the div with class ".add_staff" to display: flex
    */
    $('#position').change(function () {
        if ($(this).val() != 'Nhân viên') {
            $('.add_staff').css('display', 'flex');
        } else {
            $('.add_staff').hide();
        }
    });

});
