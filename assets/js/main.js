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
        $('#datasource_action').addClass('hide_important');
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
        $('#datasource_action').removeClass('hide_important');

        // console.log(childID);
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
    * Process when click on "#remove_datasource" button
    * 1. Hide the button
    * 2. Show the div with id "list_datasource" set to display: flex
    */
    $(document).on('click', '#remove_datasource', function (e) {
        e.preventDefault();
        var childid = $(this).data('childid');
        $('#child-' + childid).remove();
    });

    /* 
    * File: addnew_template.php
    * Process when click on "#add_formula" button
    * 1. No need hide the button
    * 2. Call ajax to show the form to add new formula
    */
    $('.add_formula').click(function (e) {
        e.preventDefault();
        var formula_count = $("#formula_count").val();
        var custom = $(this).data('custom');

        $.ajax({
            type: 'POST',
            url: AJAX.ajax_url,
            data: {
                action: 'add_formula',
                formula_count: formula_count,
                custom: custom
            },
            success: function (response) {
                var obj = JSON.parse(response);
                $('#replaceArea').append(obj.formula_div);
                $("#formula_count").val(obj.formula_count);
            }
        });
    });
    
    /* 
    * File: addnew_template.php
    * Process when click on "#remove_formula" button
    * 1. Decrease the value of input hidden with id "formula_count"
    * 2. Remove the div with class "data_replace_box"
    */
    $(document).on('click', '#remove_formula', function (e) {
        e.preventDefault();
        var formula_count = $("#formula_count").val();
        formula_count = formula_count - 1;
        $("#formula_count").val(formula_count);

        $(this).closest('.data_replace_box').remove();
    });

    /* 
    * File: addnew_template.php
    * Process when click on ".multiblock" to choose 2 datasource
    */
    $(document).on('click', '.multiblock', function (e) {
        e.preventDefault();
        var childID = $(this).data('childid');
        var multi_datasource = $('input[name="multi_datasource"]').val();
        var multi_datasource_arr = multi_datasource ? multi_datasource.split(',') : [];

        // put childID to multi_datasource_arr, if childID is in multi_datasource_arr, remove it
        // if number of element in multi_datasource_arr is greater than 2, remove the first element
        if (multi_datasource_arr.includes(childID.toString())) {
            var index = multi_datasource_arr.indexOf(childID.toString());
            if (index > -1) {
                multi_datasource_arr.splice(index, 1);
            }
        } else {
            if (multi_datasource_arr.length >= 2) {
                multi_datasource_arr.shift();
            }
            multi_datasource_arr.push(childID + '');
        }

        // remove all element with class "blockselect" in the a tag with class "multiblock"
        $('.multiblock .blockselect').remove();
        // append element with class "blockselect" to the a tag with class is "blockid-" + ID (in multi_datasource_arr)
        multi_datasource_arr.forEach( (item, index) => {
            var number = index + 1;
            $('.blockid-' + item).append('<span class="blockselect">' + number + '</span>');
        });

        // join multi_datasource_arr to string with ',', then set to input hidden name is "multi_datasource"
        $('input[name="multi_datasource"]').val(multi_datasource_arr.join(','));

    });

    $(document).on('click', '.select_multiblock', function (e) {
        var formulaID = $(this).data('formula');
        var multi_datasource = $('input[name="multi_datasource"]').val();
        // if multi_datasource is empty, return false
        if (!multi_datasource) {
            alert('Bạn chưa chọn cơ sở dữ liệu nào');
            return false;
        } else {
            // remove #list_datasource in the div with id "formula-" + formulaID
            $('#formula-' + formulaID + ' #list_datasource').remove();

            // call ajax to show the datasource are selected
            $.ajax({
                type: 'POST',
                url: AJAX.ajax_url,
                data: {
                    action: 'show_multiblock',
                    multi_datasource: multi_datasource,
                    formulaID: formulaID
                },
                success: function (response) {
                    $('#formula-' + formulaID).append(response);
                }
            });
        }
    });

    /* 
    * File: addnew_template.php, ajax select data to replace
    * Remove field when click to .remove_field link
    */
    $(document).on('click', '.remove_field', function (e) {
        e.preventDefault();
        // remove parent of this element
        $(this).parent().remove();
    });

    /* 
    * File: addnew_template.php
    * Process when submit the form with id "addnew_template_form"
    */
    // $('#create_document').submit(function (e) {
    //     e.preventDefault();
    //     var form = $(this);
    //     var formData = new FormData(form[0]);

    //     // get data from input hidden with name "ls_dataid", if not have value, return false
    //     var ls_dataid = $('input[name="ls_dataid"]').val();

    //     formData.append("action", "create_document");

    //     console.log(formData);

    //     $.ajax({
    //         type: 'POST',
    //         url: AJAX.ajax_url,
    //         data: formData,
    //         contentType: false,
    //         processData: false,
    //         beforeSend: function () {
    //             $('#create_document').addClass('hide_important');
    //             $('#create_loading').addClass('show_important');
    //         },
    //         success: function (response) {
    //             // console.log(response);
    //             $('#create_loading').html(response);

    //         }
    //     });
    // });


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
                // console.log(childID);
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
    * Process when click .choose_date to search replace data to create document
    */
    // $('.choose_date').click(function (e) {
    //     e.preventDefault();
    //     var $this = $(this);
    //     var childID = $(this).data('childid');
    //     var selectdate = $('input[name="datareplace_' + childID + '"]').val();
    //     var templateID = $('input[name="templateID"]').val();
        
    //     // console.log(selectdate);
    //     $.ajax({
    //         type: 'POST',
    //         url: AJAX.ajax_url,
    //         data: {
    //             action: 'choose_date',
    //             childID: childID,
    //             selectdate: selectdate,
    //             templateID: templateID
    //         },
    //         beforeSend: function () {
    //             $this.find('i').hide();
    //             $this.find('.loader').show();
    //         },
    //         success: function (response) {
    //             $this.find('i').show();
    //             $this.find('.loader').hide();
    //             $('#replaceResult_' + childID).html(response);
    //         }
    //     });
    // });

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
        var templateID = $('input[name="templateID"]').val();

        /* hide input search field with class .replace_search */
        $('#replaceSearch_' + childid).addClass('hide_div').hide();
        
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
                templateID: templateID,
                childid: childid,
                jsondata: jsondata
            },
            success: function (response) {
                // console.log(response);
                var obj = JSON.parse(response);
                console.log(obj.outputdata);
                $('#replaceResult_' + childid).append(obj.show);
                /* set value to input hidden name is selectdata */
                $('input[name="selectdata_' + childid + '"]').val(obj.outputdata);
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
    * File: create_document.php
    * Process when click .search_data to search replace data to create document
    */
    $(document).on('click', '.search_multidata', function (e) {
        e.preventDefault();
        var key     = $(this).data('key');
        var struct  = $('input[name="struct_' + key + '"]').val();
        var search  = $('input[name="search_' + key + '"]').val();
        var $this   = $(this);

        console.log(struct);
        
        $.ajax({
            type: 'POST',
            url: AJAX.ajax_url,
            data: {
                action: 'search_multidata',
                struct: struct,
                key: key,
                search: search
            },
            beforeSend: function () {
                $this.find('i').hide();
                $this.find('.loader').show();
                // console.log(childID);
            },
            success: function (response) {
                $this.find('i').show();
                $this.find('.loader').hide();
                $this.parents().eq(2).find('#selectResult').html(response);
            }
        });
    });

    /* process when form with id select_multidata_form submited */
    $(document).on('click', '.multiselect_data', function (e) {
        e.preventDefault();
        var multiselect = $(this).data('multiselect');
        var key         = $(this).data('key');
        var parentid    = $(this).data('parentid');
        var struct      = $('input[name="struct_' + key + '"]').val();
        var currentdata = $('input[name="custom#multidata#' + key + '"]').val();
        
        $(this).hide();


        $.ajax({
            type: 'POST',
            url: AJAX.ajax_url,
            data: {
                action: 'select_multidata',
                multiselect: multiselect,
                parentid: parentid,
                currentdata: currentdata,
                struct: struct
            },
            beforeSend: function () {
                
            },
            success: function (response) {
                var obj = JSON.parse(response);
                $('input[name="custom#multidata#' + key + '"]').val(obj.outputdata);
                $('#multiResult_' + key).html(obj.show);
            }
        });
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
