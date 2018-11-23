import { extend, isNil, isString } from '../core/util';
import { createEl, setStyle, removeDomNode } from '../core/util/dom';
import Eventable from '../core/Eventable';
import Class from '../core/Class';
import Point from '../geo/Point';
import Map from '../map/Map';


class Control extends Eventable(Class) {

    
    constructor(options) {
        if (options && options['position'] && !isString(options['position'])) {
            options['position'] = extend({}, options['position']);
        }
        super(options);
    }

   
    addTo(map) {
        this.remove();
        if (!map.options['control']) {
            return this;
        }
        this._map = map;
        const controlContainer = map._panels.control;
        this.__ctrlContainer = createEl('div');
        setStyle(this.__ctrlContainer, 'position:absolute;overflow:visible;');
      
        this.update();
        controlContainer.appendChild(this.__ctrlContainer);
        if (this.onAdd) {
            this.onAdd();
        }
        
        this.fire('add', {
            'dom': controlContainer
        });
        return this;
    }

   
    update() {
        this.__ctrlContainer.innerHTML = '';
        this._controlDom = this.buildOn(this.getMap());
        if (this._controlDom) {
            this._updatePosition();
            this.__ctrlContainer.appendChild(this._controlDom);
        }
        return this;
    }

   
    getMap() {
        return this._map;
    }

  
    getPosition() {
        return extend({}, this._parse(this.options['position']));
    }

    setPosition(position) {
        if (isString(position)) {
            this.options['position'] = position;
        } else {
            this.options['position'] = extend({}, position);
        }
        this._updatePosition();
        return this;
    }

    
    getContainerPoint() {
        const position = this.getPosition();

        const size = this.getMap().getSize();
        let x, y;
        if (!isNil(position['left'])) {
            x = parseInt(position['left']);
        } else if (!isNil(position['right'])) {
            x = size['width'] - parseInt(position['right']);
        }
        if (!isNil(position['top'])) {
            y = parseInt(position['top']);
        } else if (!isNil(position['bottom'])) {
            y = size['height'] - parseInt(position['bottom']);
        }
        return new Point(x, y);
    }

    getContainer() {
        return this.__ctrlContainer;
    }

  
    getDOM() {
        return this._controlDom;
    }

    /**
     * Show
     * @return {control.Control} this
     */
    show() {
        this.__ctrlContainer.style.display = '';
        return this;
    }

    /**
     * Hide
     * @return {control.Control} this
     */
    hide() {
        this.__ctrlContainer.style.display = 'none';
        return this;
    }

    /**
     * Whether the control is visible
     * @return {Boolean}
     */
    isVisible() {
        return (this.__ctrlContainer && this.__ctrlContainer.style.display === '');
    }

    /**
     * Remove itself from the map
     * @return {control.Control} this
     * @fires control.Control#remove
     */
    remove() {
        if (!this._map) {
            return this;
        }
        removeDomNode(this.__ctrlContainer);
        if (this.onRemove) {
            this.onRemove();
        }
        delete this._map;
        delete this.__ctrlContainer;
        delete this._controlDom;
       
        this.fire('remove');
        return this;
    }

    _parse(position) {
        let p = position;
        if (isString(position)) {
            p = Control['positions'][p];
        }
        return p;
    }

    _updatePosition() {
        let position = this.getPosition();
        if (!position) {
            //default one
            position = {
                'top': 20,
                'left': 20
            };
        }
        for (const p in position) {
            if (position.hasOwnProperty(p)) {
                position[p] = parseInt(position[p]);
                this.__ctrlContainer.style[p] = position[p] + 'px';
            }
        }
        
        this.fire('positionchange', {
            'position': extend({}, position)
        });
    }

}

Control.positions = {
    'top-left': {
        'top'   : 20,
        'left'  : 20
    },
    'top-right': {
        'top'   : 20,
        'right' : 20
    },
    'bottom-left': {
        'bottom': 20,
        'left'  : 20
    },
    'bottom-right': {
        'bottom': 20,
        'right' : 20
    }
};

Map.mergeOptions({
    'control': true
});

Map.include(this
     */
    addControl: function (control) {
        // if map container is a canvas, can't add control on it.
        if (this._containerDOM.getContext) {
            return this;
        }
        control.addTo(this);
        return this;
    },

   
    removeControl: function (control) {
        if (!control || control.getMap() !== this) {
            return this;
        }
        control.remove();
        return this;
    }

});

export default Control;
