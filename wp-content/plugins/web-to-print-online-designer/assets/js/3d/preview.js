import * as THREE from './three.module.js';
import {OrbitControls} from './OrbitControls.js';
import {GLTFLoader} from './GLTFLoader.js';

class NBD3DPreview {
  constructor(){
    this.renderer;
    this.texture;
    this.texture_material;
    this.camera;
    this.scene;
    this.inputElement;
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

    // 调整环境光，降低强度以增加对比度
    var ambient = new THREE.AmbientLight(0xffffff, 0.35);  // 降低环境光强度
    this.scene.add(ambient);

    // 调整底部补光，使其更柔和
    var bottomLight = new THREE.DirectionalLight(0xffffff, 0.2);  // 降低强度
    bottomLight.position.set(0, -2, 0.5);  // 稍微偏前
    this.scene.add(bottomLight);

    // 重新设置主要打光
    for(let i = 0; i < 4; i++){
      let spotLight = new THREE.SpotLight(0xffffff, 0.5);
      let x, y, z;
      
      if (i === 0) {
        // 正面主光源：更强、更集中
        x = 0;
        y = 2.5;
        z = 2.5;
        spotLight.intensity = 2.0;     // 增加主光源强度
        spotLight.angle = 0.5;         // 缩小光照角度使光线更集中
        spotLight.penumbra = 0.2;      // 减小半影区域增加对比度
        spotLight.decay = 1.2;         // 减小衰减
      } else if (i === 1) {
        // 右上方辅助光源：作为轮廓光
        x = 2;
        y = 2;
        z = -0.5;                      // 移到后方制造轮廓光
        spotLight.intensity = 0.8;
        spotLight.angle = 0.4;
        spotLight.penumbra = 0.3;
        spotLight.decay = 1.8;         // 增加衰减
      } else if (i === 2) {
        // 左上方辅助光源：作为填充光
        x = -1.5;
        y = 1.8;
        z = 1.5;
        spotLight.intensity = 0.9;
        spotLight.angle = 0.5;
        spotLight.penumbra = 0.4;
        spotLight.decay = 1.5;
      } else {
        // 底部补光：更精确的角度
        x = 0;
        y = -1;
        z = 1.2;
        spotLight.intensity = 0.5;
        spotLight.angle = 0.6;
        spotLight.penumbra = 0.6;      // 更柔和的边缘
        spotLight.decay = 2.0;         // 快速衰减
        spotLight.castShadow = false;
      }
      
      spotLight.position.set(x, y, z);
      spotLight.distance = 12;         // 增加光照距离
      
      if (i !== 3) {
        spotLight.castShadow = true;
        spotLight.shadow.bias = -0.0001;
        spotLight.shadow.mapSize.width = 1024;
        spotLight.shadow.mapSize.height = 1024;
        // 调整阴影相机参数以获得更清晰的阴影
        spotLight.shadow.camera.near = 0.5;
        spotLight.shadow.camera.far = 15;
        spotLight.shadow.camera.fov = 30;
      }
      
      this.scene.add(spotLight);
      this.spotLights.push(spotLight);
      
      // 精确控制光源方向
      if (i === 0) {
        spotLight.target.position.set(0, 0.8, 0);  // 主光源目标点
      } else if (i === 1) {
        spotLight.target.position.set(0, 0, 0);    // 轮廓光目标点
      } else if (i === 2) {
        spotLight.target.position.set(0, 0.5, 0);  // 填充光目标点
      }
      // 设置光源不影响特定材质
      spotLight.layers.set(1);  // 将聚光灯设置到不同的层
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
          if(obj.name == 'Ossito' ||
            obj.name == 'Cube.001_1' ||
            obj.name == 'Sphere' ||
            obj.name == 'Sphere001' ||
            obj.name == 'Sphere002'
          ){
            console.log('found mesh Ossito ---- ');
            let textureObject = obj;
            let old_material = textureObject.material;
            
            let texture = old_material.map;
            console.log('Material texture:', texture);
            
            if (!texture) {
              texture = old_material.bumpMap || 
                       old_material.normalMap || 
                       old_material.roughnessMap || 
                       old_material.metalnessMap;
            }
            const materialColor = old_material.color || new THREE.Color(1, 1, 1);

            if (!texture) {
              console.log('No texture found, creating default texture');
              const width = 1;
              const height = 1;
              const data = new Uint8Array(width * height * 4);
                            
              data[0] = Math.floor(materialColor.r * 255);
              data[1] = Math.floor(materialColor.g * 255);
              data[2] = Math.floor(materialColor.b * 255);
              data[3] = 255;
              console.log('texture color data: ---- ', data[0], ' ', data[1], ' ', data[2]);
              texture = new THREE.DataTexture(data, width, height, THREE.RGBAFormat);
              texture.needsUpdate = true;
            }

            const createFurMaterial = (layerIndex, totalLayers) => {
              return new THREE.ShaderMaterial({
                //new THREE.Color(0.98, 0.95, 0.92)
                //255,240,245
                uniforms: {
                  furTexture: { value: texture },
                  furColor: { value: new THREE.Color(materialColor.r, materialColor.g, materialColor.b) },
                  uNoiseTexture: { value: this.uNoiseTexture },
                  furLength: { value: 0.05 },
                  layerIndex: { value: layerIndex },
                  totalLayers: { value: totalLayers },
                  time: { value: 0.0 },

                  lightPosition: { value: new THREE.Vector3(1, 2, 1) },  // 添加光照位置
                  specularStrength: { value: 0.0 },   // 添加高光强度
                  ambientStrength: { value: 0.3 }     // 添加环境光强度
                },
                vertexShader: this.furVertexShader,
                fragmentShader: this.furFragmentShader,
                transparent: layerIndex > 0,
                side: THREE.DoubleSide,
                depthWrite: true,        // 所有层都写入深度
                blending: THREE.NormalBlending
              });
            };
            
            // 保存原始 mesh 的变换和父节点
            const originalParent = obj.parent;
            const originalPosition = obj.position.clone();
            const originalRotation = obj.rotation.clone();
            const originalScale = obj.scale.clone();
            
            // 清除之前的毛发层（如果存在）
            if (this.furMeshesGroups[obj.name]) {
              this.furMeshesGroups[obj.name].forEach(mesh => {
                mesh.parent.remove(mesh);
              });
            }
            this.furMeshesGroups[obj.name] = [];
            
            // 创建一个新的组来保持毛发层
            const furGroup = new THREE.Group();
            furGroup.position.copy(originalPosition);
            furGroup.rotation.copy(originalRotation);
            furGroup.scale.copy(originalScale);
            originalParent.add(furGroup);

            const LAYERS = 60;
            const baseMaterial = createFurMaterial(0, LAYERS);
            const baseMesh = new THREE.Mesh(obj.geometry, baseMaterial);
            baseMesh.renderOrder = 1000;
            furGroup.add(baseMesh);
            this.furMeshesGroups[obj.name].push(baseMesh);

            for(let i = 1; i < LAYERS; i++) {
              const material = createFurMaterial(i, LAYERS);
              const furMesh = new THREE.Mesh(obj.geometry, material);
              furMesh.renderOrder = i;
              furGroup.add(furMesh);
              this.furMeshesGroups[obj.name].push(furMesh);
            }
            // 保持原始mesh可见但完全透明
            obj.material.transparent = true;
            obj.material.opacity = 0;
            obj.material.depthWrite = true;
            obj.renderOrder = 999;  // 在毛发效果之前渲染

            this.texture_materials[obj.name] = obj.material;
            // obj.visible = false;
          } else if (obj.name == 'Sphere003' || obj.name == 'Sphere004' || obj.name == 'Sphere005') {
            // 为 Sphere003 创建金属质感材质
            let baseObject = obj;
            let old_material = baseObject.material;

            var metalMaterial = new THREE.MeshPhysicalMaterial({
              color: old_material.color || 0x888888,
              metalness: 0.6,          // 降低金属度，使反射更柔和
              roughness: 0.2,          // 降低粗糙度，但保持适中以产生扩散效果
              reflectivity: 0.7,       // 降低反射率，使光晕更自然
              clearcoat: 0.5,          // 降低清漆层强度
              clearcoatRoughness: 0.2, // 增加清漆层粗糙度
              envMapIntensity: 0.8,    // 降低环境贴图强度
              ior: 1.3,               // 添加折射率参数
              transmission: 0.2,       // 添加透射效果
              thickness: 0.5           // 材质厚度
            });

            // 创建环境贴图
            const cubeRenderTarget = new THREE.WebGLCubeRenderTarget(256, {
              minFilter: THREE.LinearMipmapLinearFilter,
              magFilter: THREE.LinearFilter,
              generateMipmaps: true,
              type: THREE.HalfFloatType
            });            
            const cubeCamera = new THREE.CubeCamera(0.1, 10, cubeRenderTarget);
            this.scene.add(cubeCamera);

            // 更新环境贴图的函数
            const updateEnvironmentMap = () => {
              baseObject.visible = false;
              cubeCamera.position.copy(baseObject.position);
              cubeCamera.update(this.renderer, this.scene);
              baseObject.visible = true;
              metalMaterial.envMap = cubeRenderTarget.texture;
            };

            // 首次更新环境贴图
            updateEnvironmentMap();

            // 添加到渲染循环
            this.updateEnvironmentMap = updateEnvironmentMap;

            baseObject.material = metalMaterial;
            this.texture_materials[obj.name] = metalMaterial;

            // 确保正确的渲染顺序和深度设置
            baseObject.renderOrder = 0;
            baseObject.material.depthWrite = true;
            baseObject.material.depthTest = true;
          } else {
            let baseObject = obj;
            let old_material = baseObject.material;
            
            // 为每个mesh创建独立的材质和纹理
            let meshTexture = old_material.map;
            if (!meshTexture && old_material.color) {
              const width = 1;
              const height = 1;
              const data = new Uint8Array(width * height * 4);
              const materialColor = old_material.color;
              data[0] = Math.floor(materialColor.r * 255);
              data[1] = Math.floor(materialColor.g * 255);
              data[2] = Math.floor(materialColor.b * 255);
              data[3] = 255;
              meshTexture = new THREE.DataTexture(data, width, height, THREE.RGBAFormat);
              meshTexture.needsUpdate = true;
            }

            var new_base_material = new THREE.MeshPhysicalMaterial({
              color: old_material.color,
              map: meshTexture,  // 使用mesh自己的纹理
              alphaMap: old_material.alphaMap,
              transparent: old_material.transparent,
              roughness: 0.5,
              metalness: 0.0
            });
            baseObject.material = new_base_material;
            this.texture_materials[obj.name] = new_base_material;
            obj.renderOrder = 0;  // 最先渲染非毛发mesh
            obj.material.depthWrite = true;
            obj.material.depthTest = true;
          }
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

  update_design(design, context){
    const _this = this;
    if( context == 'on_worker' ){
      console.log('update_design on_worker');

      let loader = new THREE.ImageBitmapLoader();
      loader.load(design, function(imageBitmap ) {
        _this.texture = new THREE.CanvasTexture( imageBitmap );
        _this.texture.flipY = false;
        Object.values(_this.furMeshesGroups).forEach(meshGroup => {
          meshGroup.forEach(mesh => {
            if(mesh.material.uniforms && mesh.material.uniforms.furTexture) {
              mesh.material.uniforms.furTexture.value = _this.texture;
            }
          });
        });
        
        _this.texture.needsUpdate = true;
        _this.render();
      });
    }else{
      console.log('update_design THREE.Texture');
      const image = new Image();
      this.texture = new THREE.Texture(image);
      this.texture.flipY = false;
      image.onload = () => {
        Object.values(this.furMeshesGroups).forEach(meshGroup => {
          meshGroup.forEach(mesh => {
            if(mesh.material.uniforms && mesh.material.uniforms.furTexture) {
              mesh.material.uniforms.furTexture.value = this.texture;
            }
          });
        });
        _this.texture.needsUpdate = true;
        _this.render();
      };
      image.src = design;
    }
  }
}
export default NBD3DPreview;