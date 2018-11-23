import { createEl } from '../core/util/dom';
import Control from './Control';
import Map from '../map/Map';


const options = {
    'position': {
        'bottom': 0,
        'left': 0
    },
    'content': '<a href="http://maptalks.org" target="_blank">maptalks</a>'
};

const layerEvents = 'addlayer removelayer setbaselayer baselayerremove';

class Attribution extends Control {

    buildOn() {
        this._attributionContainer = createEl('div');
        this._attributionContainer.className = 'maptalks-attribution';
        this._update();
        return this._attributionContainer;
    }

    onAdd() {
        this.getMap().on(layerEvents, this._update, this);
    }

    onRemove() {
        this.getMap().off(layerEvents, this._update, this);
    }

    _update() {
        const map = this.getMap();
        if (!map) {
            return;
        }

        const attributions = map
            ._getLayers(layer => layer.options['attribution'])
            .reverse()
            .map(layer => layer.options['attribution']);
        const content = this.options['content'] + (attributions.length > 0 ? ' - ' + attributions.join(', ') : '');
        this._attributionContainer.innerHTML = '<span style="padding:0px 4px">' + content + '</span>';
    }
}

Attribution.mergeOptions(options);

Map.mergeOptions({
    'attribution': true
});

Map.addOnLoadHook(function () {
    const a = this.options['attribution'] || this.options['attributionControl'];
    if (a) {
        this.attributionControl = new Attribution(a);
        this.addControl(this.attributionControl);
    }
});

export default Attribution;
