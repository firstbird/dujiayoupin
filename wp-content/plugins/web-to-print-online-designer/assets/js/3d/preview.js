import * as THREE from './three.module.js';
import {OrbitControls} from './OrbitControls.js';
import {GLTFLoader} from './GLTFLoader.js';

class NBD3DPreview {
  constructor(inputElement, settings) {
    this.renderer;
    this.texture;
    this.texture_material;
    this.camera;
    this.scene;
    this.inputElement = inputElement;
    this.controls;
    this.renderRequested = false;
    this.spotLights = [];
    this.particles;
    this.particleParams;
    this.furLength = 2.0;
    this.furDensity = 2.0;
    this.furGravity = 0.9;
    this.furTime = 0;
    this.furMeshes = [];  // 添加数组来存储毛发网格
    this.furMeshesGroups = {};
    this.texture_materials = {};
    this.original_custom_materials = {};
    this.settings = settings;

    // 将captureModelImages函数暴露到window对象
    // try {
    //   if (typeof window !== 'undefined') {
    //     window.NBD3DPreviewCapture = () => {
    //       console.log('从window对象调用captureModelImages');
    //       this.captureModelImages();
    //     };
    //   }
    // } catch (error) {
    //   console.error('暴露函数到window对象时出错:', error);
    // }

    // // 添加消息监听
    // console.log('开始设置消息监听器');
    // window.addEventListener('message', function(event) {
    //     console.log('preview.js收到消息:', event.data);
    //     console.log('消息来源:', event.source);
    //     console.log('消息类型:', event.data.type);
    //     console.log('消息数据:', event.data.data);
        
    //     if (event.data.type === 'capture_model_images') {
    //         console.log('收到截图请求');
    //         var design = event.data.data.design;
    //         if (design) {
    //             console.log('开始处理设计数据');
    //             // 处理设计数据
    //             _this.processDesignData(design);
    //         }
    //     }
    // });
    // console.log('消息监听器设置完成');
  }

  init(data) {
    const {canvas, inputElement, model, settings, callback} = data;
    this.renderer = new THREE.WebGLRenderer({canvas: canvas, antialias: true});
    const _this = this;
    const fov = 75;
    const aspect = 2;
    const near = 0.1;
    const far = 5;
    this.camera = new THREE.PerspectiveCamera(fov, aspect, near, far);
    this.camera.position.z = 2;
    this.inputElement = inputElement;

    this.controls = new OrbitControls(this.camera, this.inputElement);
    this.controls.enableDamping = true;
    this.controls.target.set(0, 0, 0);
    this.controls.update();

    this.scene = new THREE.Scene();
    this.scene.background = new THREE.Color('#808080');
    this.settings = settings;

    // 环境光
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.5);
    this.scene.add(ambientLight);

    // 主光源 - 模拟太阳光
    const mainLight = new THREE.DirectionalLight(0xffffff, 1);
    mainLight.position.set(5, 5, 5);
    mainLight.castShadow = true;
    this.scene.add(mainLight);

    // 填充光 - 柔和的补光
    const fillLight = new THREE.DirectionalLight(0xffffff, 0.3);
    fillLight.position.set(-5, 0, -5);
    this.scene.add(fillLight);

    // 背光 - 增加轮廓感
    const backLight = new THREE.DirectionalLight(0xffffff, 0.2);
    backLight.position.set(0, -5, -5);
    this.scene.add(backLight);

    // 设置阴影
    mainLight.shadow.mapSize.width = 2048;
    mainLight.shadow.mapSize.height = 2048;
    mainLight.shadow.camera.near = 0.1;
    mainLight.shadow.camera.far = 100;
    mainLight.shadow.bias = -0.001;

    // 启用阴影
    this.renderer.shadowMap.enabled = true;
    this.renderer.shadowMap.type = THREE.PCFSoftShadowMap;
    // 这个是保持颜色正常显示的修改
    this.renderer.outputEncoding = THREE.sRGBEncoding;

    for(let i = 0; i < 4; i++){
      let spotLight = new THREE.SpotLight( 0xffffff, 1 );
      let x = ( i % 2 == 0 ) ? 1 : - 1;
      let z = ( i % 4 > 1 ) ? -1 : 1;
      spotLight.position.set( x, 2, z );
      spotLight.angle = 0.50;
      spotLight.penumbra = 0.75;
      spotLight.intensity = 1;
      spotLight.decay = 3;
      this.scene.add(spotLight);
      this.spotLights.push(spotLight);
    }
    
    function frameArea(sizeToFitOnScreen, boxSize, boxCenter, camera) {
      const halfSizeToFitOnScreen = sizeToFitOnScreen * 0.5;
      const halfFovY = THREE.MathUtils.degToRad(camera.fov * .5);
      const distance = halfSizeToFitOnScreen / Math.tan(halfFovY);
      const direction = (new THREE.Vector3())
          .subVectors(camera.position, boxCenter)
          .multiply(new THREE.Vector3(1, 0, 1))
          .normalize();

      camera.position.copy(direction.multiplyScalar(distance).add(boxCenter));

      camera.near = boxSize / 100;
      camera.far = boxSize * 100;

      camera.updateProjectionMatrix();

      camera.lookAt(boxCenter.x, boxCenter.y, boxCenter.z);

      _this.spotLights.map(spotLight => spotLight.position.multiplyScalar(sizeToFitOnScreen / 2));
      _this.spotLights.map(spotLight => spotLight.target.position.set(boxCenter.x, boxCenter.y, boxCenter.z));
      _this.spotLights.map(spotLight => spotLight.target.updateMatrixWorld());
    }

    const createNoiseTexture = () => {
      const size = 256;
      const data = new Uint8Array(size * size * 4);
      for (let i = 0; i < size * size; i++) {
        const value = Math.random() * 255;
        data[i * 4] = data[i * 4 + 1] = data[i * 4 + 2] = value;
        data[i * 4 + 3] = 255;
      }
      const texture = new THREE.DataTexture(data, size, size, THREE.RGBAFormat);
      texture.needsUpdate = true;
      return texture;
    };
    
    this.uNoiseTexture = createNoiseTexture();

    this.furVertexShader = `
      varying vec3 vPosition;
      varying vec3 vNormal;
      varying vec2 vUv;
      varying vec3 vViewPosition;
      uniform float furLength;
      uniform float layerIndex;
      uniform float totalLayers;
      uniform sampler2D uNoiseTexture;
      uniform float time;
      
      void main() {
        vUv = uv;
        vNormal = normalize(normalMatrix * normal);
        
        float heightFactor = pow(layerIndex / totalLayers, 1.2);
        
        vec2 noiseUv = uv * 20.0;
        float noise = texture2D(uNoiseTexture, noiseUv + time * 0.01).r;
        
        vec3 offset = normal * (furLength * heightFactor * (0.95 + noise * 0.1));
        
        vec3 sideVector = normalize(cross(normal, vec3(0.0, 1.0, 0.0)));
        vec3 upVector = normalize(cross(normal, sideVector));
        
        float windEffect = sin(time + position.x * 5.0 + position.y * 3.0) * 0.01;
        offset += sideVector * windEffect * heightFactor;
        
        vec3 newPosition = position + offset;
        
        vec4 mvPosition = modelViewMatrix * vec4(newPosition, 1.0);
        vViewPosition = -mvPosition.xyz;
        vPosition = newPosition;
        gl_Position = projectionMatrix * mvPosition;
      }
    `;

    this.furFragmentShader = `
      varying vec3 vPosition;
      varying vec3 vNormal;
      varying vec2 vUv;
      varying vec3 vViewPosition;
      
      uniform sampler2D furTexture;
      uniform sampler2D uNoiseTexture;
      uniform vec3 furColor;
      uniform float time;
      uniform float layerIndex;
      uniform float totalLayers;
      
      uniform vec3 lightPosition;
      uniform float specularStrength;
      uniform float ambientStrength;

      #define SHELL_LAYERS 16.0
      #define SHELL_DISTANCE 0.003
      
      float sqrlen(vec2 v) { return dot(v, v); }

      float rand(vec2 n) { 
        return fract(sin(dot(n, vec2(12.9898, 4.1414))) * 43758.5453);
      }
      
      float noise(vec2 p) {
        return texture2D(uNoiseTexture, p).r;
      }
      
      void main() {
        // 使用更亮的基础颜色
        // vec3 baseColor = vec3(0.98, 0.95, 0.92);
        vec3 baseColor = furColor;
        float heightFactor = layerIndex / totalLayers;
        
        // 第一层使用相近的颜色
        if(layerIndex < 1.0) {
          // baseColor = vec3(0.96, 0.93, 0.90);
          baseColor = furColor * 0.9;
        }
        // 环境光
        vec3 lightColor = vec3(1.0, 1.0, 1.0);
        vec3 ambient = ambientStrength * lightColor;
        
        // 漫反射
        vec3 normal = normalize(vNormal);
        vec3 lightDir = normalize(lightPosition - vPosition);
        float diff = max(dot(normal, lightDir), 0.0);
        vec3 diffuse = diff * lightColor;
        
        // 高光
        vec3 viewDir = normalize(-vViewPosition);
        vec3 reflectDir = reflect(-lightDir, normal);
        float spec = pow(max(dot(viewDir, reflectDir), 0.0), 32.0);
        vec3 specular = specularStrength * spec * vec3(1.0);

        // 简化的噪声计算
        vec2 noiseUv = vUv * 30.0;
        float n1 = noise(noiseUv + time * 0.1);
        float n2 = noise(noiseUv * 1.5 - time * 0.15) * 0.5;
        float combinedNoise = (n1 + n2) * 0.7 + 0.3;
        
        // 柔和的光照计算 old light
        // vec3 normal = normalize(vNormal);
        // vec3 lightDir = normalize(vec3(1.0, 1.0, 1.0));
        // float diffuse = dot(normal, lightDir) * 0.25 + 0.75;  // 减少光照变化
        
        // 平滑的毛发图案
        float furPattern = smoothstep(0.3, 0.7, combinedNoise);
        
        // 柔和的渐变
        float tipGradient = pow(1.0 - heightFactor, 1.2);
        tipGradient = smoothstep(0.2, 0.8, tipGradient);
        
        // 颜色计算 - 保持明亮
        vec3 finalColor = ambient + baseColor * diffuse + specular;
        finalColor += vec3(0.05) * tipGradient;  // 轻微的高光
        finalColor = max(finalColor, baseColor * 0.95);  // 确保最小亮度
        
        // 透明度计算
        float alpha;
        if(layerIndex < 1.0) {
          alpha = 1.0;
        } else {
          alpha = furPattern;
          alpha *= pow(1.0 - heightFactor, 0.7);
          alpha = smoothstep(0.2, 0.6, alpha);
          alpha = alpha * 0.9 + 0.1;  // 保持较高的基础透明度
        }
        
        gl_FragColor = vec4(finalColor, alpha);
      }
    `;

    const gltfLoader = new GLTFLoader();
    gltfLoader.load(model, (gltf) => {
      const root = gltf.scene;
      this.scene.add(root);
      // 保存原始模型的变换
      console.log('gltf loaded meshName: ---- ', settings.meshName);
      root.traverse((obj) => {
        console.log('obj.name ---- ', obj.name , ' obj.isMesh: ', obj.isMesh);
        if (obj.isMesh) {
          // if(obj.name == 'Ossito' ||
          //   obj.name == 'Cube.001_1' ||
          //   obj.name == 'Sphere' ||
          //   obj.name == 'Sphere001' ||
          //   obj.name == 'Sphere002'
          // ){
          //   console.log('found mesh Ossito ---- ');
          //   let textureObject = obj;
          //   let old_material = textureObject.material;
            
          //   let texture = old_material.map;
          //   console.log('Material texture:', texture);
            
          //   if (!texture) {
          //     texture = old_material.bumpMap || 
          //              old_material.normalMap || 
          //              old_material.roughnessMap || 
          //              old_material.metalnessMap;
          //   }
          //   const materialColor = old_material.color || new THREE.Color(1, 1, 1);

          //   if (!texture) {
          //     console.log('No texture found, creating default texture');
          //     const width = 1;
          //     const height = 1;
          //     const data = new Uint8Array(width * height * 4);
                            
          //     data[0] = Math.floor(materialColor.r * 255);
          //     data[1] = Math.floor(materialColor.g * 255);
          //     data[2] = Math.floor(materialColor.b * 255);
          //     data[3] = 255;
          //     console.log('texture color data: ---- ', data[0], ' ', data[1], ' ', data[2]);
          //     texture = new THREE.DataTexture(data, width, height, THREE.RGBAFormat);
          //     texture.needsUpdate = true;
          //   }

          //   const createFurMaterial = (layerIndex, totalLayers) => {
          //     return new THREE.ShaderMaterial({
          //       //new THREE.Color(0.98, 0.95, 0.92)
          //       //255,240,245
          //       uniforms: {
          //         furTexture: { value: texture },
          //         furColor: { value: new THREE.Color(materialColor.r, materialColor.g, materialColor.b) },
          //         uNoiseTexture: { value: this.uNoiseTexture },
          //         furLength: { value: 0.05 },
          //         layerIndex: { value: layerIndex },
          //         totalLayers: { value: totalLayers },
          //         time: { value: 0.0 },

          //         lightPosition: { value: new THREE.Vector3(1, 2, 1) },  // 添加光照位置
          //         specularStrength: { value: 0.0 },   // 添加高光强度
          //         ambientStrength: { value: 0.3 }     // 添加环境光强度
          //       },
          //       vertexShader: this.furVertexShader,
          //       fragmentShader: this.furFragmentShader,
          //       transparent: layerIndex > 0,
          //       side: THREE.DoubleSide,
          //       depthWrite: true,        // 所有层都写入深度
          //       blending: THREE.NormalBlending
          //     });
          //   };
            
          //   // 保存原始 mesh 的变换和父节点
          //   const originalParent = obj.parent;
          //   const originalPosition = obj.position.clone();
          //   const originalRotation = obj.rotation.clone();
          //   const originalScale = obj.scale.clone();
            
          //   // 清除之前的毛发层（如果存在）
          //   if (this.furMeshesGroups[obj.name]) {
          //     this.furMeshesGroups[obj.name].forEach(mesh => {
          //       mesh.parent.remove(mesh);
          //     });
          //   }
          //   this.furMeshesGroups[obj.name] = [];
            
          //   // 创建一个新的组来保持毛发层
          //   const furGroup = new THREE.Group();
          //   furGroup.position.copy(originalPosition);
          //   furGroup.rotation.copy(originalRotation);
          //   furGroup.scale.copy(originalScale);
          //   originalParent.add(furGroup);

          //   const LAYERS = 60;
          //   const baseMaterial = createFurMaterial(0, LAYERS);
          //   const baseMesh = new THREE.Mesh(obj.geometry, baseMaterial);
          //   baseMesh.renderOrder = 1000;
          //   furGroup.add(baseMesh);
          //   this.furMeshesGroups[obj.name].push(baseMesh);

          //   for(let i = 1; i < LAYERS; i++) {
          //     const material = createFurMaterial(i, LAYERS);
          //     const furMesh = new THREE.Mesh(obj.geometry, material);
          //     furMesh.renderOrder = i;
          //     furGroup.add(furMesh);
          //     this.furMeshesGroups[obj.name].push(furMesh);
          //   }
          //   // 保持原始mesh可见但完全透明
          //   obj.material.transparent = true;
          //   obj.material.opacity = 0;
          //   obj.material.depthWrite = true;
          //   obj.renderOrder = 999;  // 在毛发效果之前渲染

          //   this.texture_materials[obj.name] = obj.material;
          //   // obj.visible = false;
          // } else if (obj.name == 'Sphere003' || obj.name == 'Sphere004' || obj.name == 'Sphere005') {
          //   // 为 Sphere003 创建金属质感材质
          //   let baseObject = obj;
          //   let old_material = baseObject.material;

          //   var metalMaterial = new THREE.MeshPhysicalMaterial({
          //     color: old_material.color || 0x888888,
          //     metalness: 0.6,          // 降低金属度，使反射更柔和
          //     roughness: 0.2,          // 降低粗糙度，但保持适中以产生扩散效果
          //     reflectivity: 0.7,       // 降低反射率，使光晕更自然
          //     clearcoat: 0.5,          // 降低清漆层强度
          //     clearcoatRoughness: 0.2, // 增加清漆层粗糙度
          //     envMapIntensity: 0.8,    // 降低环境贴图强度
          //     ior: 1.3,               // 添加折射率参数
          //     transmission: 0.2,       // 添加透射效果
          //     thickness: 0.5           // 材质厚度
          //   });

          //   // 创建环境贴图
          //   const cubeRenderTarget = new THREE.WebGLCubeRenderTarget(256, {
          //     minFilter: THREE.LinearMipmapLinearFilter,
          //     magFilter: THREE.LinearFilter,
          //     generateMipmaps: true,
          //     type: THREE.HalfFloatType
          //   });            
          //   const cubeCamera = new THREE.CubeCamera(0.1, 10, cubeRenderTarget);
          //   this.scene.add(cubeCamera);

          //   // 更新环境贴图的函数
          //   const updateEnvironmentMap = () => {
          //     baseObject.visible = false;
          //     cubeCamera.position.copy(baseObject.position);
          //     cubeCamera.update(this.renderer, this.scene);
          //     baseObject.visible = true;
          //     metalMaterial.envMap = cubeRenderTarget.texture;
          //   };

          //   // 首次更新环境贴图
          //   updateEnvironmentMap();

          //   // 添加到渲染循环
          //   this.updateEnvironmentMap = updateEnvironmentMap;

          //   baseObject.material = metalMaterial;
          //   this.texture_materials[obj.name] = metalMaterial;

          //   // 确保正确的渲染顺序和深度设置
          //   baseObject.renderOrder = 0;
          //   baseObject.material.depthWrite = true;
          //   baseObject.material.depthTest = true;
          // } else {
            console.log('obj.name: ', obj.name, ' settings.meshName: ', settings.meshName);
            if(obj.name == settings.meshName ){ // custom mesh
              let baseObject = obj;
              let old_material = baseObject.material;
              
              // 创建新的StandardMaterial以支持PBR渲染
              var new_base_material = new THREE.MeshStandardMaterial({
                color: old_material.color,
                metalness: old_material.metalness || 0.5,
                roughness: old_material.roughness || 0.5,
                envMapIntensity: 1.0
              });

              // 保留原始材质的所有贴图
              if(old_material.map) new_base_material.map = old_material.map;
              if(old_material.normalMap) new_base_material.normalMap = old_material.normalMap;
              if(old_material.roughnessMap) new_base_material.roughnessMap = old_material.roughnessMap;
              if(old_material.metalnessMap) new_base_material.metalnessMap = old_material.metalnessMap;
              if(old_material.emissiveMap) {
                new_base_material.emissiveMap = old_material.emissiveMap;
                new_base_material.emissive = old_material.emissive;
                new_base_material.emissiveIntensity = old_material.emissiveIntensity;
              }

              // 创建环境贴图
              const pmremGenerator = new THREE.PMREMGenerator(_this.renderer);
              const envMap = pmremGenerator.fromScene(_this.scene).texture;
              new_base_material.envMap = envMap;
              pmremGenerator.dispose();

              baseObject.material = new_base_material;
              _this.original_custom_materials[obj.name] = new_base_material;

            }else{
              let baseObject = obj;
              let old_material = baseObject.material;
              
              // 对其他物体也使用StandardMaterial
              var new_base_material = new THREE.MeshStandardMaterial({
                color: old_material.color,
                metalness: old_material.metalness || 0.3,
                roughness: old_material.roughness || 0.7
              });
              
              if(old_material.map) new_base_material.map = old_material.map;
              baseObject.material = new_base_material;
            }
          // }
        }
      });

      root.updateMatrixWorld(true);

      // 打印场景中的所有对象
      console.log('Scene hierarchy:');
      this.scene.traverse((object) => {
          console.log('Object:', object.name, 'Type:', object.type, 'Visible:', object.visible);
      });

      const box = new THREE.Box3().setFromObject(root);
      const boxSize = box.getSize(new THREE.Vector3()).length();
      const boxCenter = box.getCenter(new THREE.Vector3());

      frameArea(boxSize * 2, boxSize, boxCenter, this.camera);

      this.controls.maxDistance = boxSize * 10;
      this.controls.target.copy(boxCenter);
      this.controls.update();

      callback( "loaded_3d_model" );
    });

    this.render();
    function _render(){
      _this.render();
    }

    function requestRenderIfNotRequested() {
      if (!_this.renderRequested) {
        _this.renderRequested = true;
        requestAnimationFrame(_render);
      }
    }

    this.controls.addEventListener('change', requestRenderIfNotRequested);
  }

  resizeRendererToDisplaySize(renderer) {
    const canvas = renderer.domElement;
    const width = this.inputElement.clientWidth;
    const height = this.inputElement.clientHeight;
    const needResize = canvas.width !== width || canvas.height !== height;
    if (needResize) {
      this.renderer.setSize(width, height, false);
    }
    return needResize;
  }

  render() {
    this.renderRequested = undefined;

    if (this.resizeRendererToDisplaySize(this.renderer)) {
      this.camera.aspect = this.inputElement.clientWidth / this.inputElement.clientHeight;
      this.camera.updateProjectionMatrix();
    }

    this.controls.update();

    if (this.particles && this.particleParams) {
      const positionsArray = this.particles.geometry.attributes.position.array;
    
      for (let i = 0; i < this.particleParams.count; i++) {
        positionsArray[i * 3 + 1] -= this.particleParams.physics.gravity * 0.01;
      }
    
      this.particles.geometry.attributes.position.needsUpdate = true;
    }

    if (this.texture_materials) {
      Object.values(this.furMeshesGroups).forEach(meshGroup => {
        meshGroup.forEach(mesh => {
          if(mesh.material.uniforms && mesh.material.uniforms.furTexture) {
            mesh.material.uniforms.furTexture.value = this.texture;
          }
        });
      });
    }

    this.renderer.render(this.scene, this.camera);
  }

  // 捕获模型不同角度的图像
  captureModelImages() {
    console.log('captureModelImages ---- ');
    // 保存原始相机状态
    try {
      // 创建离屏canvas并获取WebGL上下文
      const offscreenCanvas = new OffscreenCanvas(1024, 1024);
      const gl = offscreenCanvas.getContext('webgl2') || offscreenCanvas.getContext('webgl');
      
      if (!gl) {
          throw new Error('无法获取WebGL上下文');
      }

      console.log('创建offscreenRenderer开始');
      // 创建离屏渲染器时传入完整的参数
      const offscreenRenderer = new THREE.WebGLRenderer({ 
          canvas: offscreenCanvas,
          context: gl,
          antialias: true,
          alpha: true,
          preserveDrawingBuffer: true
      });
      console.log('offscreenRenderer:', offscreenRenderer);

      // 手动设置内部属性
      if (!offscreenRenderer.domElement) {
          offscreenRenderer.domElement = offscreenCanvas;
      }
      
      // 设置尺寸前先检查
      if (typeof offscreenRenderer._width === 'undefined') {
          offscreenRenderer._width = 1024;
          offscreenRenderer._height = 1024;
      }

      console.log('设置渲染器尺寸前');
      // 使用setViewport替代setSize
      offscreenRenderer.setViewport(0, 0, 1024, 1024);
      console.log('设置渲染器尺寸后');

      offscreenRenderer.setClearColor(0x000000, 0);
      offscreenRenderer.outputEncoding = THREE.sRGBEncoding;
      
      // 创建离屏相机
      const offscreenCamera = new THREE.PerspectiveCamera(45, 1, 0.1, 1000);
      
      // 获取模型的包围盒
      const box = new THREE.Box3().setFromObject(this.scene);
      const boxCenter = box.getCenter(new THREE.Vector3());
      const boxSize = box.getSize(new THREE.Vector3());
      const maxDim = Math.max(boxSize.x, boxSize.y, boxSize.z);
      const distance = maxDim * 2;
      
      // 定义视图
      const views = [
          { name: 'front', position: new THREE.Vector3(0, 0, distance) },
          { name: 'back', position: new THREE.Vector3(0, 0, -distance) },
          { name: 'left', position: new THREE.Vector3(-distance, 0, 0) },
          { name: 'right', position: new THREE.Vector3(distance, 0, 0) },
          { name: 'top', position: new THREE.Vector3(0, distance, 0) },
          { name: 'bottom', position: new THREE.Vector3(0, -distance, 0) }
          // 暂时只测试一个视图
      ];

      // 捕获图像
      views.forEach(view => {
        console.log('渲染视图:', view.name);
        // 设置相机
        offscreenCamera.position.copy(view.position);
        offscreenCamera.lookAt(boxCenter);
        
        // 渲染场景
        offscreenRenderer.render(this.scene, offscreenCamera);
        
        // 获取canvas数据
        offscreenCanvas.convertToBlob({
            type: 'image/png'
        }).then(blob => {
            // 创建ArrayBuffer来传输数据
            const reader = new FileReader();
            reader.onload = () => {
                const arrayBuffer = reader.result;
                console.log('post capture_image arrayBuffer ---- ', arrayBuffer);
                // 发送ArrayBuffer（可转移对象）
                self.postMessage({
                    type: 'capture_image',
                    name: `model_${view.name}.png`,
                    data: arrayBuffer
                }, [arrayBuffer]); // 将arrayBuffer作为可转移对象传递
            };
            reader.readAsArrayBuffer(blob);
        });
      });

      // 清理资源
      offscreenRenderer.dispose();
      gl.getExtension('WEBGL_lose_context').loseContext();
      
      console.log('捕获完成');
      self.postMessage({ type: 'capture_complete' });

    } catch (error) {
        console.error('捕获模型图像时出错:', error);
        self.postMessage({
            type: 'capture_error',
            error: error.message
        });
    }
  }

  initParticleSystem(params) {
    const geometry = new THREE.BufferGeometry();
    const positions = new Float32Array(params.count * 3);
    geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
  
    const material = new THREE.PointsMaterial({
      size: 0.1,
      color: 0xffffff,
    });
  
    this.particles = new THREE.Points(geometry, material);
    this.scene.add(this.particles);
  
    const positionsArray = this.particles.geometry.attributes.position.array;
    for (let i = 0; i < params.count; i++) {
      positionsArray[i * 3] = Math.random() * 10 - 5;
      positionsArray[i * 3 + 1] = Math.random() * 10 - 5;
      positionsArray[i * 3 + 2] = Math.random() * 10 - 5;
    }
    this.particles.geometry.attributes.position.needsUpdate = true;
    console.log('initParticleSystem ---- ', this.particles);
  }
  capture_design(design, context){
    const _this = this;
    console.log('capture_design ---- ', design);
    this.captureModelImages();
  }
  update_design(design, context){
    const _this = this;

    const applyTextureToOssito = (imageSource, ossitoMesh) => {
        try {
          console.log('开始处理贴图');
          if (!ossitoMesh) {
              console.error('找不到Ossito mesh');
              return;
          }
          
          const originalMaterial = ossitoMesh.material;
          const previewWidth = 400;
          const previewHeight = 400;
          
          // 创建预览canvas
          const previewCanvas = new OffscreenCanvas(previewWidth, previewHeight);
          const previewCtx = previewCanvas.getContext('2d');
          
          // 清除canvas并设置为透明
          previewCtx.clearRect(0, 0, previewWidth, previewHeight);
          
          // 将原图绘制到预览canvas，保持透明度
          previewCtx.globalCompositeOperation = 'source-over';
          previewCtx.drawImage(imageSource, 0, 0, imageSource.width, imageSource.height);
          
          // 创建最终的canvas
          const canvas = new OffscreenCanvas(1024, 1024);
          const ctx = canvas.getContext('2d');
          
          // 清除canvas并设置为透明
          ctx.clearRect(0, 0, canvas.width, canvas.height);
          
          // 在中心绘制预览图片，保持透明度
          const x = (canvas.width - previewWidth) / 2;
          const y = (canvas.height - previewHeight) / 2;
          ctx.drawImage(previewCanvas, x, y);
          
          // 创建新纹理
          const texture = new THREE.CanvasTexture(canvas);
          texture.premultiplyAlpha = true;
          texture.needsUpdate = true;
          
          // 创建新的材质，启用透明
          const newMaterial = new THREE.MeshPhysicalMaterial({
            color: 0xFFFFFF,
            map: texture,
            transparent: true,
            alphaTest: 0.1,
            
            // 调整这些参数使材质更亮
            roughness: originalMaterial.roughness || 0.3,     // 降低粗糙度
            metalness: originalMaterial.metalness || 0.0,     // 降低金属度
            envMapIntensity: originalMaterial.envMapIntensity || 1.5,  // 增加环境贴图强度
            reflectivity: originalMaterial.reflectivity || 1.0,        // 增加反射率
            clearcoat: originalMaterial.clearcoat || 0.3,             // 降低清漆层
            clearcoatRoughness: originalMaterial.clearcoatRoughness || 0.0,  // 降低清漆层粗糙度
            
            // 添加发光属性使材质更亮
            emissive: originalMaterial.emissive || new THREE.Color(0x333333),
            emissiveIntensity: originalMaterial.emissiveIntensity || 0.2,
            
            side: THREE.DoubleSide,
            shadowSide: THREE.FrontSide
          });
          
          // 设置纹理属性
          texture.encoding = THREE.sRGBEncoding;
          
          // 应用新材质
          ossitoMesh.material = newMaterial;
          ossitoMesh.material.needsUpdate = true;
          
          // 更新材质映射
          _this.texture_materials[_this.settings.meshName] = newMaterial;
          
          console.log('贴图已更新');
          _this.render();
          
      } catch (error) {
          console.error('处理贴图时发生错误:', error);
      }
    };

    if (context == 'on_worker') {
        console.log('使用worker模式加载图片, design ', design);
        let loader = new THREE.ImageBitmapLoader();
        // let ossitoMesh = this.originalMeshes[this.settings.meshName];
        let ossitoMesh = this.scene.getObjectByName(this.settings.meshName);//Ossito
        if (ossitoMesh) {
          ossitoMesh.material = this.original_custom_materials[this.settings.meshName];
        }
        // 会导致再次打开时preview不是最新的
        // if (!ossitoMesh) {
        //   ossitoMesh = this.scene.getObjectByName(this.settings.meshName);//Ossito
        //   this.originalMeshes[this.settings.meshName] = ossitoMesh.clone();
        // }

        // const ossitoMesh = this.originalMeshes['Ossito001'];
        loader.load(design, function(imageBitmap) {
            console.log('ImageBitmap已加载:', imageBitmap);
            applyTextureToOssito(imageBitmap, ossitoMesh);
            // _this.captureModelImages();
        });
    } else {
        console.log('使用普通模式加载图片');
        const image = new Image();
        image.crossOrigin = 'anonymous';
        image.onload = () => applyTextureToOssito(image);
        image.onerror = (err) => {
            console.error('图片加载失败:', err);
        };
        image.src = design;
    }
  }
}
export default NBD3DPreview;