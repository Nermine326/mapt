import { extend, isNil, isString } from '../core/util';
import { createEl } from '../core/util/dom';
import DragHandler from '../handler/Drag';
import Control from './Control';


const options = {
    'position': 'top-right',
    'draggable': true,
    'custom': false,
    'content': '',
    'closeButton': true
};



class Panel extends Control {

    
    buildOn() {
        let dom;
        if (this.options['custom']) {
            if (isString(this.options['content'])) {
                dom = createEl('div');
                dom.innerHTML = this.options['content'];
            } else {
                dom = this.options['content'];
            }
        } else {
            dom = createEl('div', 'maptalks-panel');
            if (this.options['closeButton']) {
                const closeButton = createEl('a', 'maptalks-close');
                closeButton.href = 'javascript:;';
                closeButton.onclick = function () {
                    dom.style.display = 'none';
                };
                dom.appendChild(closeButton);
            }

            const panelContent = createEl('div', 'maptalks-panel-content');
            panelContent.innerHTML = this.options['content'];
            dom.appendChild(panelContent);
        }

        this.draggable = new DragHandler(dom, {
            'cancelOn': this._cancelOn.bind(this),
            'ignoreMouseleave' : true
        });

        this.draggable.on('dragstart', this._onDragStart, this)
            .on('dragging', this._onDragging, this)
            .on('dragend', this._onDragEnd, this);

        if (this.options['draggable']) {
            this.draggable.enable();
        }

        return dom;
    }

   
    update() {
        if (this.draggable) {
            this.draggable.disable();
            delete this.draggable;
        }
        return Control.prototype.update.call(this);
    }

   
    setContent(content) {
        const old = this.options['content'];
        this.options['content'] = content;
        
        this.fire('contentchange', {
            'old': old,
            'new': content
        });
        if (this.isVisible()) {
            this.update();
        }
        return this;
    }

    getContent() {
        return this.options['content'];
    }

    _cancelOn(domEvent) {
        const target = domEvent.srcElement || domEvent.target,
            tagName = target.tagName.toLowerCase();
        if (tagName === 'button' ||
            tagName === 'input' ||
            tagName === 'select' ||
            tagName === 'option' ||
            tagName === 'textarea') {
            return true;
        }
        return false;
    }

    _onDragStart(param) {
        this._startPos = param['mousePos'];
        this._startPosition = extend({}, this.getPosition());
               this.fire('dragstart', param);
    }

    _onDragging(param) {
        const pos = param['mousePos'];
        const offset = pos.sub(this._startPos);

        const startPosition = this._startPosition;
        const position = this.getPosition();
        if (!isNil(position['top'])) {
            position['top'] = parseInt(startPosition['top']) + offset.y;
        }
        if (!isNil(position['bottom'])) {
            position['bottom'] = parseInt(startPosition['bottom']) - offset.y;
        }
        if (!isNil(position['left'])) {
            position['left'] = parseInt(startPosition['left']) + offset.x;
        }
        if (!isNil(position['right'])) {
            position['right'] = parseInt(startPosition['right']) - offset.x;
        }
        this.setPosition(position);
        
        this.fire('dragging', param);
    }

    _onDragEnd(param) {
        delete this._startPos;
        delete this._startPosition;
       
        this.fire('dragend', param);
    }

   
    _getConnectPoints() {
        const map = this.getMap();
        const containerPoint = this.getContainerPoint();
        const dom = this.getDOM(),
            width = parseInt(dom.clientWidth),
            height = parseInt(dom.clientHeight);
        const anchors = [
            //top center
            map.containerPointToCoordinate(
                containerPoint.add(width / 2, 0)
            ),
            //middle right
            map.containerPointToCoordinate(
                containerPoint.add(width, height / 2)
            ),
            //bottom center
            map.containerPointToCoordinate(
                containerPoint.add(width / 2, height)
            ),
            //middle left
            map.containerPointToCoordinate(
                containerPoint.add(0, height / 2)
            )

        ];
        return anchors;
    }

}

Panel.mergeOptions(options);

export default Panel;
