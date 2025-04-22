import {EventDispatcher} from './three.module.js';
import NBD3DPreview from './preview.js';

function noop() {
}

class ElementProxyReceiver extends EventDispatcher {
  constructor() {
    super();
  }
  get clientWidth() {
    return this.width;
  }
  get clientHeight() {
    return this.height;
  }
  getBoundingClientRect() {
    return {
      left: this.left,
      top: this.top,
      width: this.width,
      height: this.height,
      right: this.left + this.width,
      bottom: this.top + this.height,
    };
  }
  handleEvent(data) {
    if (data.type === 'size') {
      this.left = data.left;
      this.top = data.top;
      this.width = data.width;
      this.height = data.height;
      return;
    }
    data.preventDefault = noop;
    data.stopPropagation = noop;
    this.dispatchEvent(data);
  }
  focus() {
    // no-op
  }
}

class ProxyManager {
  constructor() {
    this.targets = {};
    this.handleEvent = this.handleEvent.bind(this);
  }
  makeProxy(data) {
    const {id} = data;
    const proxy = new ElementProxyReceiver();
    this.targets[id] = proxy;
  }
  getProxy(id) {
    return this.targets[id];
  }
  handleEvent(data) {
    this.targets[data.id].handleEvent(data.data);
  }
}

const proxyManager = new ProxyManager();
let preview = new NBD3DPreview();

function start(data) {
  const proxy = proxyManager.getProxy(data.canvasId);
  proxy.ownerDocument = proxy;
  self.document = {};
  // 创建一个模拟的document对象
  self.document = {
    createElementNS: (ns, tagName) => {
        if (tagName === 'canvas') {
            // 对于canvas元素，返回传入的offscreen canvas
            return data.canvas;
        }
        // 对于其他元素，创建一个基本的模拟对象
        return {
            style: {},
            addEventListener: () => {},
            removeEventListener: () => {},
            setAttribute: () => {},
            getElementsByTagName: () => [],
            createElement: () => ({
                style: {},
                addEventListener: () => {},
                removeEventListener: () => {}
            })
        };
    },
    createElement: (tagName) => {
        if (tagName === 'canvas') {
            // 对于canvas元素，返回传入的offscreen canvas
            return data.canvas;
        }
        // 对于其他元素，创建一个基本的模拟对象
        return {
            style: {},
            addEventListener: () => {},
            removeEventListener: () => {},
            setAttribute: () => {}
        };
    },
    addEventListener: () => {},
    removeEventListener: () => {},
    documentElement: {
        style: {}
    }
  };

  // 添加必要的window属性
  self.window = self;
  self.innerWidth = data.canvas.width;
  self.innerHeight = data.canvas.height;
  preview.init({
    canvas: data.canvas,
    inputElement: proxy,
    model: data.model,
    settings: data.settings,
    callback: loaded_3d_model
  });
}

function update_design(data){
  preview.update_design(data.design, 'on_worker');
}

function capture_design(data){
  preview.capture_design(data.design, 'on_worker');
}

function makeProxy(data) {
  proxyManager.makeProxy(data);
}

function loaded_3d_model( msg ){
  postMessage(msg);
}

const handlers = {
  start,
  update_design,
  capture_design,
  // 添加新的handler
  capture_image: function(data) {
    postMessage({
        type: 'capture_image',
        name: data.name,
        data: data.data
    }, [data.data]); // 转移数据所有权
  },
  makeProxy,
  event: proxyManager.handleEvent,
};

self.onmessage = function(e) {
  const fn = handlers[e.data.type];
  if (!fn) {
    throw new Error('no handler for type: ' + e.data.type);
  }
  fn(e.data);
};