
let baseURL = "";
if (process.env.NODE_ENV === 'development') {
    // 开发环境
    baseURL = 'http://wedev2.gdoooa.com';
} else {
    // 生产环境
    baseURL = 'http://wedev2.gdoooa.com';
}

console.log(baseURL);

//post 封装
function post(url, data) {
    var promise = new Promise((resolve, reject) => {
        var me = this;
        var params = data;
			
		uni.showLoading({
			mask: true,
			title: '数据处理中...'
		});	
		
        uni.request({
            url: baseURL + '/' + url,
            data: params,
            method: 'POST',
            header: {
				//'content-type': 'application/json',
				"accept": "application/json",
				'content-type': 'application/x-www-form-urlencoded',
				'x-auth-token': uni.getStorageSync('token')
            },
            success: function(res) {
               resolve(res.data);
            },
            error: function(error) {
               reject(error.data);
            },
			complete: function() {
			    uni.hideLoading();
				return;
			}
        })
    })
    return promise;
}

//get 封装
function get(url, data) {
   var promise = new Promise((resolve, reject) => {
       var me = this;
       var params = data;
       uni.request({
            url: baseURL + '/' + url,
            data: params,
            method: 'GET',
            header: {
               'content-type': 'application/json',
			   'x-auth-token': uni.getStorageSync('token')
            },
            success: function(res) {
               resolve(res.data);
            },
            error: function(error) {
               reject(error.data);
            },
			complete: function() {
			    uni.hideLoading();
				return;
			}
       })
   })
   return promise;
}

function authorize() {
	var token = uni.getStorageSync('token');
	if (token) {
		uni.switchTab({
			url: '/pages/tabbar/notice'
		});
	} else {
		if (isWeiXin()) {
			post('wap/wechat/config').then(res => {
				if (res.status) {
					wxAuthorize(res.data);
				} else {
					uni.showModal({
					    title: '错误',
					    content: res.data
					});
				}
			}).catch(res => {
			});
		} else {
			uni.reLaunch({
				url:'/pages/login/wap'
			});
		}
	}
}

function wxAuthorize(config) {
	var params = getParams();
	if (params.code) {
		// 去掉url的参数
		var host = location.href.split('?')[0];
		history.pushState({}, 0, host);
		post('wap/wechat/authorize', {code: params.code}).then(res => {
			uni.setStorageSync('openid', res.data.openid);
			if (res.status) {
				uni.setStorageSync('access', res.data.access);
				uni.setStorageSync('token', res.data.token);
				uni.setStorageSync('user', res.data.user);
				uni.reLaunch({
					url: '/pages/tabbar/notice'
				});
			} else {
				uni.showModal({
					title: '错误',
					content: res.data
				});
				uni.reLaunch({
					url:'/pages/login/wechat'
				});
			}
		}).catch(res => {
			uni.showModal({
				title: '错误',
				content: res.data
			});
		});
	} else {
		let appid = config.appid;
		let uri = encodeURIComponent(window.location.href);
		window.location.href = `https://open.weixin.qq.com/connect/oauth2/authorize?appid=${appid}&redirect_uri=${uri}&response_type=code&scope=snsapi_base&state=gdoooa#wechat_redirect`;  
	}  
}

function logout() {
	var me = this;
	if (isWeiXin()) {
		var openid = uni.getStorageSync('openid');
		if (openid == '') {
			uni.showToast({
				title: 'Openid不能为空。'
			});
			return;
		}
		uni.showModal({
			title: '警告',
			content: '确定要解绑帐号吗？',
			success: function (btn) {
				if (btn.confirm) {
					post('wap/wechat/logout', {openid: openid}).then(res => {
						if (res.status) {
							uni.removeStorageSync('access');
							uni.removeStorageSync('token');
							uni.removeStorageSync('user');
							uni.reLaunch({
								url:'/pages/index'
							});
						}
					});
				} else if (btn.cancel) {
				}
			}
		});
	} else {
		uni.showModal({
			title: '警告',
			content: '确定要注销帐号吗？',
			success: function (btn) {
				if (btn.confirm) {
					post('wap/auth/logout').then(res => {
						uni.removeStorageSync('access');
						uni.removeStorageSync('token');
						uni.removeStorageSync('user');
						uni.reLaunch({
							url:'/pages/index'
						});
					});
				} else if (btn.cancel) {
				}
			}
		});
	}
}

function isWeiXin() {
    var ua = window.navigator.userAgent.toLowerCase();
    if (ua.match(/MicroMessenger/i) == 'micromessenger') {
		return true; // 是微信端
    } else {
		return false;
    }
}

function getParams() {
	var query = window.location.search.substring(1);
	var vars = query.split("&");
	var ret = {};
	for (var i = 0; i < vars.length;i++) {
		var pair = vars[i].split("=");
		ret[pair[0]] = pair[1];
	}
	return ret;
}

/** 压缩图片
 * @param {Object} file 上传对象files[0]
 * @param {Object} options 压缩设置对象
 * @param {Function} callback 回调函数
 * @result {Object} 返回blob文件对象
 * */
var compressImg = function(file, options, callback) {
    var self = this;
    var imgname = file.name;
    var imgtype = (imgname.substring(imgname.lastIndexOf('.') + 1)).toLowerCase();
    if(imgtype == 'jpg' || imgtype == 'jpeg') {
        imgtype = 'image/jpeg';
    } else {
        imgtype = 'image/png';
    }
    // 用FileReader读取文件
    var reader = new FileReader();
    // 将图片读取为base64
    reader.readAsDataURL(file);
    reader.onload = function(evt) {
        var base64 = evt.target.result;
        // 创建图片对象
        var img = new Image();
        // 用图片对象加载读入的base64
        img.src = base64;
        img.onload = function () {
            var that = this,
            canvas = document.createElement('canvas'),
            ctx = canvas.getContext('2d');
            canvas.setAttribute('width', that.width);
            canvas.setAttribute('height', that.height);
            // 将图片画入canvas
            ctx.drawImage(that, 0, 0, that.width, that.height);
            
            // 压缩到指定体积以下（M）
            if (options.size) {
                var scale = 0.9;
                (function f(scale) {
                    if (base64.length / 1024 / 1024 > options.size && scale > 0) {
                        base64 = canvas.toDataURL(imgtype, scale);
                        scale = scale - 0.1;
                        f(scale);
                    } else {
                        callback(base64);
                    }
                })(scale); 
            } else if(options.scale) {
                // 按比率压缩
                base64 = canvas.toDataURL(imgtype, options.scale);
                callback(base64);
            }
            
        }
    }
};

module.exports = {
   compressImg,
   post,
   get,
   getParams,
   baseURL,
   authorize,
   logout,
   isWeiXin
}