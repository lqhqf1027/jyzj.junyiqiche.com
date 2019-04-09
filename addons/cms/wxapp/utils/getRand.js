function getRand(obj, field){
	this.obj = obj;
	this.field = field || 'prob'
	return this.init();
}

//获取几率总和
getRand.prototype.sum = function(key){
	var self = this;
	var obj = self.obj;
	var sum=0;
	obj.forEach(function(n) {
		sum+=n[key];
	})
	return sum;
};

//取得结果
getRand.prototype.init = function(){
	var result = null;
	var self = this;
	var obj = self.obj.filter(function(n) { return parseFloat(n[self.field]) > 0 });
	var sum = self.sum(self.field);	//几率总和
	for (var i = 0; i < obj.length; i ++) {
		var rand = parseInt(Math.random()*sum);
		if(rand<=obj[i][self.field]){
			result = obj[i];
			break;
		}else{
			sum-=obj[i][self.field];
		}
	}
	return result;
}

export default getRand