$(function(){
    $('.custom-timepicker').bootstrapMaterialDatePicker({
      date: false,
      format: 'HH:mm',
      shortTime: false,
      clearButton: true,
      nowButton: false,
      switchOnClick: true,
      cancelText: 'Cancelar',
      minuteStep: 10,
      showMeridian: false,
      explicitMode: true,
      changeMinute: 10,
      
    }); 
    $('.custom-datepicker').datepicker({
      showOn: 'both',
      buttonText: '<i class="far fa-calendar"></i>',
      dateFormat: 'dd/mm/yy',
    });
});