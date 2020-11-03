wp.blocks.registerBlockType('gaoo/block', {
  title: 'Google Analytics Opt-Out',
  category: 'common',
  edit: function (props) {
    return ('[ga_optout]');
  },
  save: function (props) {
    return ('[ga_optout]');
  }
});