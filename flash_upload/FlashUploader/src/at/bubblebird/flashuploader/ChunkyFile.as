package at.bubblebird.flashuploader 
{
	import flash.events.*;
	import flash.net.*
	import flash.utils.ByteArray;
	/**
	 * ...
	 * @author ...
	 */
	public class ChunkyFile extends EventDispatcher {
		
		public static const UPLOAD_MODE_MULTIPART:String = 'multipart';
		public static const UPLOAD_MODE_SOCKET:String = 'socket';
		
		private var fileReference:FileReference;
		private var chunkSize:uint;
		private var chunkPos:uint = 0;
		private var chunkCount:uint = 0;
		private var totalChunkCount:uint = 0;
		private var mode:String;
		private var target:String;
		private var urlStream:URLStream;
		
		public function ChunkyFile(fileReference:FileReference, chunkSize:uint = 256) {
			this.fileReference = fileReference;
			this.chunkSize = chunkSize * 1024;
			fileReference.addEventListener(Event.COMPLETE, handleFileComplete);
		}
		
		public function upload(url:String, mode:String):void {
			this.mode = mode;
			this.target = url;
			this.chunkPos = 0;
			this.chunkCount = 0;
			fileReference.load();
		}
		
		private function uploadChunk():void {
			var chunk:ByteArray = new ByteArray();
			
			this.fileReference.data.readBytes(chunk, 0, this.chunkPos + this.chunkSize > fileReference.data.length ? fileReference.data.length - this.chunkPos : this.chunkSize);

			var request:URLRequest  = new URLRequest(this.target);
			request.method = URLRequestMethod.POST;

			chunkCount++;
			
			if (this.mode == UPLOAD_MODE_MULTIPART) {
				var boundary:String = '----thefoundationupload' + new Date().getTime();
				var dashdash:String = '--';
				var crlf:String = '\r\n';
				var multipartBlob:ByteArray = new ByteArray();
				var postVars:Object = new Object();

				postVars["name"] = this.fileReference.name;
				postVars["chunk"] = this.chunkCount;
				postVars["chunks"] = this.totalChunkCount;
				
				request.requestHeaders.push(new URLRequestHeader("Content-Type", 'multipart/form-data; boundary=' + boundary));

				// Append parameters
				for (var name:String in postVars) {
					multipartBlob.writeUTFBytes(
						dashdash + boundary + crlf +
						'Content-Disposition: form-data; name="' + name + '"' + crlf + crlf +
						postVars[name] + crlf
					);
				}

				// Add file header
				multipartBlob.writeUTFBytes(
					dashdash + boundary + crlf +
					'Content-Disposition: form-data; name="Filedata"; filename="' + this.fileReference.name + '"' + crlf +
					'Content-Type: application/octet-stream' + crlf + crlf
				);
				
				// Add file data
				multipartBlob.writeBytes(chunk, 0, chunk.length);

				// Add file footer
				multipartBlob.writeUTFBytes(crlf + dashdash + boundary + dashdash + crlf);
				request.data = multipartBlob;
			} else if (this.mode == UPLOAD_MODE_SOCKET) {
				var url:String = target;
				if (url.indexOf('?') == -1){
					url += '?';
				} else {
					url += '&';
				}
				
				url += "name=" + encodeURIComponent(this.fileReference.name);
				url += "&chunk=" + this.chunkCount + "&chunks=" + this.totalChunkCount;
				
				request = new URLRequest(url);
				request.method = URLRequestMethod.POST;
				
				request.requestHeaders.push(new URLRequestHeader("Content-Type", "application/octet-stream"));
				request.data = chunk;
			}
			
			chunkPos += chunk.length;	
			chunk.clear();
			
			this.urlStream = new URLStream();

			this.urlStream.addEventListener(IOErrorEvent.IO_ERROR, function(e:IOErrorEvent):void {
				dispatchEvent(new ChunkEvent(ChunkEvent.CHUNK_ERROR));
				dispatchEvent(e);
			});

			this.urlStream.addEventListener(HTTPStatusEvent.HTTP_STATUS, function(e:HTTPStatusEvent):void {
				dispatchEvent(e);
			});
			
			this.urlStream.addEventListener(SecurityErrorEvent.SECURITY_ERROR, function(e:SecurityErrorEvent):void {
				dispatchEvent(new ChunkEvent(ChunkEvent.CHUNK_ERROR));
				dispatchEvent(e);
			});
			
			this.urlStream.addEventListener(Event.COMPLETE, function(e:Event):void {
				if (chunkPos >= fileReference.size) {
					//upload done
					dispatchEvent(new ChunkEvent(ChunkEvent.CHUNK_COMPLETE));
					dispatchEvent(new ProgressEvent(ProgressEvent.PROGRESS, true, false, chunkPos, fileReference.size));
					dispatchEvent(new Event(Event.COMPLETE, true, false));
					fileReference.data.clear();
				} else {
					dispatchEvent(new ChunkEvent(ChunkEvent.CHUNK_COMPLETE));
					dispatchEvent(new ProgressEvent(ProgressEvent.PROGRESS, true, false, chunkPos, fileReference.size));
					uploadChunk();
				}
			});
			
			this.urlStream.load(request);
		}
		
		private function handleFileComplete(e:Event):void {
			this.totalChunkCount = Math.ceil(this.fileReference.size / this.chunkSize);
			e.stopPropagation();
			uploadChunk();
		}
		
		public function get name():String {
			return this.fileReference.name;
		}
		
		public function get size():Number {
			return this.fileReference.size;
		}
	}

}