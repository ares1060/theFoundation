package at.bubblebird.flashuploader
{
	import flash.display.Sprite;
	import flash.events.*;
	import flash.net.*;
	import flash.external.*;
	import flash.display.*;
	
	/**
	 * ...
	 * @author IB
	 */
	public class Main extends Sprite 
	{
		
		public function Main():void 
		{
			if (stage) init();
			else addEventListener(Event.ADDED_TO_STAGE, init);
		}
		
		private var upTo:URLRequest;
		private var fileQueue:Array = [];
		private var uploadCount:int;
		private var maxUploadCount:int;
		private var frl:FileReferenceList;
		private var uploadTarget:String;
		private var maxFileSize:int;
		private var mode:String;
		private var chunkSize:uint;
		
		private function init(e:Event = null):void 
		{
			removeEventListener(Event.ADDED_TO_STAGE, init);
			
			stage.align = StageAlign.TOP_LEFT;
			stage.scaleMode = StageScaleMode.EXACT_FIT;
			
			var invisi:Sprite = new Sprite();
			invisi.graphics.beginFill(0x000000, 0);
			invisi.graphics.drawRect(0, 0, 100, 100);
			
			var button:SimpleButton = new SimpleButton(invisi, invisi, invisi, invisi);
			this.addChild(button);
			button.useHandCursor = true;
			
			uploadTarget = "upload.php";
			if (stage.loaderInfo.parameters.upload_target) uploadTarget = stage.loaderInfo.parameters.upload_target;
			
			this.maxUploadCount = 3;
			if (stage.loaderInfo.parameters.max_upload_count) this.maxUploadCount = Math.max(1, stage.loaderInfo.parameters.max_upload_count);
			
			this.maxFileSize = 2 * 1024 * 1024; // 2MB
			if (stage.loaderInfo.parameters.max_file_size) this.maxFileSize = stage.loaderInfo.parameters.max_file_size * 1024;
			
			this.mode = ChunkyFile.UPLOAD_MODE_SOCKET;
			if (stage.loaderInfo.parameters.multipart) this.mode = ChunkyFile.UPLOAD_MODE_MULTIPART;
			
			this.chunkSize = 256;
			
			upTo = new URLRequest(uploadTarget);
			
			frl = new FileReferenceList();
			
			frl.addEventListener(Event.SELECT, handleSelect);
			
			if (ExternalInterface.available) {
				ExternalInterface.call("handleReady");
				ExternalInterface.addCallback("startUpload", startUpload);
				ExternalInterface.addCallback("removeFile", removeFile);
				ExternalInterface.addCallback("clearQueue", clearQueue);
			}
			
			button.addEventListener(MouseEvent.MOUSE_OVER, function(e:Event):void {
				if (ExternalInterface.available) ExternalInterface.call("handleOver");
			});
			
			button.addEventListener(MouseEvent.MOUSE_OUT, function(e:Event):void {
				if (ExternalInterface.available) ExternalInterface.call("handleOut");
			});
			
			button.addEventListener(MouseEvent.MOUSE_DOWN, function(e:Event):void {
				if (ExternalInterface.available) ExternalInterface.call("handleDown");
			});
			
			button.addEventListener(MouseEvent.MOUSE_DOWN, function(e:Event):void {
				if (ExternalInterface.available) ExternalInterface.call("handleUp");
			});

			button.addEventListener(MouseEvent.CLICK, function(e:Event):void {
				browseForFiles();
			});
		}
		
		private function browseForFiles():void {
			frl.browse([new FileFilter("Images (*.jpg;*.jpeg;*.gif;*.png)", "*.jpg;*.jpeg;*.gif;*.png")]);
		}
		
		private function handleSelect(e:Event):void {
			var file:FileReference;
			for (var i:uint = 0; i < frl.fileList.length; i++) {
				file = FileReference(frl.fileList[i]);
				if (fileQueue.length < maxUploadCount) queueFile(new ChunkyFile(file, chunkSize));
				else {
					ExternalInterface.call("handleErrorFile", file.name, "MAX_UPLOAD_COUNT_REACHED");
					break;
				}
			}
		}
		
		private function startUpload():void {
			uploadNextFile();
		}
		
		private function removeFile(name:String):void {
			var f:ChunkyFile;
			var fQ:Array = fileQueue;
			fileQueue = [];
			while (fQ.length > 0) {
				f = fQ.pop() as ChunkyFile;
				if (f.name != name) fileQueue.unshift(f);
			}
		}
		
		private function clearQueue():void {
			fileQueue = [];
		}
		
		private function queueFile(f:ChunkyFile):void {
			
			f.addEventListener(Event.COMPLETE, handleFileComplete);
			f.addEventListener(IOErrorEvent.IO_ERROR, handleFileError);
			f.addEventListener(ProgressEvent.PROGRESS, handleProgress);
			f.addEventListener(HTTPStatusEvent.HTTP_STATUS, handleHttp);
			f.addEventListener(ChunkEvent.CHUNK_COMPLETE, handleChunk);
			f.addEventListener(ChunkEvent.CHUNK_ERROR, handleChunk);
			
			var add:Boolean = true;
			
			add = (f.size <= this.maxFileSize);
			
			if(add){
				for each(var fr:ChunkyFile in fileQueue) {
					if (fr.name == f.name) {
						add = false;
						break;
					}
				}
			} else {
				ExternalInterface.call("handleErrorFile", f.name, "FILE_TOO_BIG");
				return;
			}
			
			if (add) {
				fileQueue.push(f);
				ExternalInterface.call("handleAddFile", f.name);
			} else {
				ExternalInterface.call("handleErrorFile", f.name, "FILE_DUPLICATE");
				return;
			}
		}
		
		private function uploadNextFile():void {
			if (uploadCount < maxUploadCount) {
				if(fileQueue.length > 0){
					uploadCount++;
					var f:ChunkyFile = fileQueue.shift() as ChunkyFile;
					f.upload(uploadTarget, mode);
					ExternalInterface.call("handleUploadFile", f.name);
				} else if (uploadCount == 0) {
					handleFinished();
				}
			}
		}
		
		private function handleFileComplete(e:Event):void {
			uploadCount--;
			ExternalInterface.call("handleFinishedFile", e.target.name);
			uploadNextFile();
		}
		
		private function handleFileError(e:IOErrorEvent):void {
			uploadCount--;
			ExternalInterface.call("handleErrorFile", e.target.name, e.toString());
			uploadNextFile();
		}
		
		private function handleProgress(e:ProgressEvent):void {
			ExternalInterface.call("handleProgressFile", e.target.name, Math.round((e.bytesLoaded / e.bytesTotal)*100));
		}
		
		private function handleFinished():void {
			ExternalInterface.call("handleUploadFinished");
		}
		
		private function handleHttp(e:HTTPStatusEvent):void {
			ExternalInterface.call("handleHttpStatus", e.toString());
		}
		
		private function handleChunk(e:ChunkEvent):void {
			if (e.type == ChunkEvent.CHUNK_COMPLETE) ExternalInterface.call("handleChunkFinished");
			else if (e.type == ChunkEvent.CHUNK_ERROR) ExternalInterface.call("handleChunkError")
		}
	}
	
}