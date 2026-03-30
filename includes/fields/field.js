(function ($) {
  var Field = acf.models.SelectField.extend({
    type: 'address_lookup',
  });

  acf.registerFieldType(Field);
})(jQuery);
