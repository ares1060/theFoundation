package at.bubblebird.flashuploader 
{
	
	import flash.events.Event;
	
	/**
	 * ChunkEvents
	 * @author Immanuel Bauer
	 */
	public class ChunkEvent extends Event 
	{
		
		public static const CHUNK_COMPLETE:String = 'chunkComplete';
		public static const CHUNK_ERROR:String = 'chunkError';
		
		public function ChunkEvent(type:String) {
			super(type, true, false);
		}
		
	}

}