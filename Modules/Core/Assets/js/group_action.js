$(function() {
    // Select Sale Type
    $('#selectType').change(function() {
        var selectType = $('#selectType');
        var divAgency = $('#typeAgency');
        var divCompany = $('#typeCompany');
        if (selectType.val() == 1) {
            divAgency.show();
            divCompany.hide();
        } else if (selectCat.val() == 0) {
            divAgency.hide();
            divCompany.show();
        } else {
            divAgency.hide();
            divCompany.hide();
        }
    });
})
