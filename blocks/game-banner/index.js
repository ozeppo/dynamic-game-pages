import { registerBlockType } from '@wordpress/blocks';
import { TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

registerBlockType('dgp/game-banner', {
  title: __('Game Banner', 'dynamic-game-pages'),
  icon: 'games',
  category: 'widgets',
  attributes: {
    appid: { type: 'string' },
    button: { type: 'string', default: '' }
  },
  edit({ attributes, setAttributes }) {
    return (
      <>
        <TextControl
          label="Steam App ID"
          value={attributes.appid}
          onChange={(val) => setAttributes({ appid: val })}
        />
        <TextControl
          label="Button Label"
          value={attributes.button}
          onChange={(val) => setAttributes({ button: val })}
        />
      </>
    );
  },
  save() {
    return null;
  }
});