$(document).ready(function(){
    $('#addmodal, #unsubmodal').on('hidden.bs.modal', function () {
        $('#addmodal .modal-title').html("");
        $('#addmodal .modal-body').html("processing...");
        $('#proceedbtn').hide();
        $('#cancelbtn').html('Close');
        
    });
    $('#proceedbtn').click(function(){
        $('#listselect').submit();
        $('#unsubmodal .modal-title').html("");
        $('#unsubmodal .modal-body').html("processing...");
        $(this).hide();
        $('#cancelbtn').hide();
    });
});