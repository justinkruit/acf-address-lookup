(function ($) {
  var Field = acf.models.SelectField.extend({
    type: 'address_lookup',
    // name: 'myprescription_expiration_date',
    events: {
      'click .date_plus_30': 'onClick',
    },
    onClick: function (e, $el) {
      e.preventDefault();
      alert('cat');
    },
  });

  acf.registerFieldType(Field);
})(jQuery);
