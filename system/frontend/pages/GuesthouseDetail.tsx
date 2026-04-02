
import React, { useState, useEffect } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import { ArrowLeft, ExternalLink, X, ChevronLeft, ChevronRight } from 'lucide-react';
import SEO from '../components/SEO';
import { publicApi } from '../lib/api';

interface Guesthouse {
  id: number;
  name: string;
  description: string | null;
  short_description: string | null;
  image_path: string | null;
  images: string[] | null;
  link: string | null;
}

const GuesthouseDetail: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [guesthouse, setGuesthouse] = useState<Guesthouse | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [selectedImageIndex, setSelectedImageIndex] = useState<number | null>(null);
  const [isZoomed, setIsZoomed] = useState(false);

  const displayImages = guesthouse?.images && guesthouse.images.length > 0 
    ? guesthouse.images.map(img => `/storage/${img}`)
    : guesthouse?.image_path 
      ? [`/storage/${guesthouse.image_path}`]
      : [];

  const handlePrev = (e?: React.MouseEvent) => {
    e?.stopPropagation();
    if (selectedImageIndex === null) return;
    setSelectedImageIndex((prev) => 
      prev === 0 ? displayImages.length - 1 : (prev as number) - 1
    );
    setIsZoomed(false);
  };

  const handleNext = (e?: React.MouseEvent) => {
    e?.stopPropagation();
    if (selectedImageIndex === null) return;
    setSelectedImageIndex((prev) => 
      prev === displayImages.length - 1 ? 0 : (prev as number) + 1
    );
    setIsZoomed(false);
  };

  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if (selectedImageIndex === null) return;
      
      switch (e.key) {
        case 'ArrowLeft':
          handlePrev();
          break;
        case 'ArrowRight':
          handleNext();
          break;
        case 'Escape':
          setSelectedImageIndex(null);
          setIsZoomed(false);
          break;
      }
    };

    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, [selectedImageIndex, displayImages.length]);

  useEffect(() => {
    const fetchGuesthouse = async () => {
      if (!id) {
        setError('無效的民宿 ID');
        setLoading(false);
        return;
      }

      try {
        const response = await publicApi.guesthouses.get(id);
        setGuesthouse(response.data);
      } catch (error) {
        console.error('Failed to fetch guesthouse:', error);
        setError('載入民宿資訊失敗');
      } finally {
        setLoading(false);
      }
    };

    fetchGuesthouse();
  }, [id]);

  if (loading) {
    return (
      <div className="animate-in fade-in duration-700 bg-[#f0f4ff] min-h-screen">
        <div className="container mx-auto px-4 sm:px-6 max-w-4xl py-12 sm:py-16 md:py-24">
          <div className="flex justify-center items-center py-8 sm:py-12">
            <div className="text-gray-400">載入中...</div>
          </div>
        </div>
      </div>
    );
  }

  if (error || !guesthouse) {
    return (
      <div className="animate-in fade-in duration-700 bg-[#f0f4ff] min-h-screen">
        <div className="container mx-auto px-4 sm:px-6 max-w-4xl py-12 sm:py-16 md:py-24">
          <div className="bg-[#f0f4ff] rounded-[30px] sm:rounded-[35px] md:rounded-[40px] shadow-sm border border-gray-100 p-6 sm:p-8 md:p-12 text-center">
            <p className="text-gray-500 mb-4 sm:mb-6 text-sm sm:text-base">{error || '找不到此民宿'}</p>
            <Link
              to="/guidelines"
              className="inline-flex items-center gap-2 text-teal-600 hover:text-black transition-colors text-sm sm:text-base"
            >
              <ArrowLeft size={16} className="sm:w-[18px] sm:h-[18px]" />
              <span>返回租車須知</span>
            </Link>
          </div>
        </div>
      </div>
    );
  }

  const structuredData = guesthouse ? {
    '@context': 'https://schema.org',
    '@type': 'LodgingBusiness',
    name: guesthouse.name,
    description: guesthouse.description || guesthouse.short_description || '小琉球精選民宿',
    image: guesthouse.image_path ? `${window.location.origin}/storage/${guesthouse.image_path}` : undefined,
    url: guesthouse.link || `${window.location.origin}/guesthouses/${guesthouse.id}`
  } : undefined;

  return (
    <div className="animate-in fade-in duration-700 bg-[#f0f4ff] min-h-screen">
      {guesthouse && (
        <SEO
          title={`${guesthouse.name} - 民宿推薦 - 蘭光電動機車`}
          description={guesthouse.short_description || guesthouse.description || '小琉球精選合作民宿'}
          keywords={`${guesthouse.name},小琉球民宿,合作民宿,小琉球住宿`}
          url={`/guesthouses/${guesthouse.id}`}
          image={guesthouse.image_path ? `/storage/${guesthouse.image_path}` : undefined}
          structuredData={structuredData}
        />
      )}
      <header className="py-12 sm:py-16 md:py-20 px-4 sm:px-6 bg-[#f0f4ff]">
        <div className="container mx-auto max-w-4xl">
          <Link
            to="/guidelines"
            className="inline-flex items-center gap-2 text-gray-500 hover:text-black transition-colors mb-4 sm:mb-6 text-sm sm:text-base"
          >
            <ArrowLeft size={16} className="sm:w-[18px] sm:h-[18px]" />
            <span>返回租車須知</span>
          </Link>
          <p className="text-gray-400 tracking-[0.3em] uppercase mb-2 text-xs sm:text-sm">Partner Stays</p>
          <h1 className="text-2xl sm:text-3xl md:text-4xl lg:text-5xl xl:text-6xl serif font-light mb-3 sm:mb-4">{guesthouse.name}</h1>
          {guesthouse.short_description && (
            <p className="text-gray-500 text-sm sm:text-base md:text-lg">{guesthouse.short_description}</p>
          )}
        </div>
      </header>

      <section className="container mx-auto px-4 sm:px-6 max-w-4xl pb-12 sm:pb-16 md:pb-24">
        <div className="bg-white rounded-[30px] sm:rounded-[35px] md:rounded-[40px] shadow-sm p-6 sm:p-8 md:p-12 overflow-hidden">
          {/* 顯示多圖片或主圖片 */}
          {guesthouse.images && Array.isArray(guesthouse.images) && guesthouse.images.length > 0 ? (
            <div className="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 mb-8">
              {guesthouse.images.map((img, idx) => (
                <div 
                  key={idx} 
                  className="aspect-[4/3] overflow-hidden rounded-lg cursor-pointer hover:opacity-95 transition-opacity"
                  onClick={() => setSelectedImageIndex(idx)}
                >
                  <img
                    src={`/storage/${img}`}
                    alt={`${guesthouse.name} ${idx + 1}`}
                    className="w-full h-full object-cover"
                  />
                </div>
              ))}
            </div>
          ) : guesthouse.image_path ? (
            <div 
              className="aspect-[16/9] overflow-hidden rounded-lg mb-8 cursor-pointer hover:opacity-95 transition-opacity"
              onClick={() => setSelectedImageIndex(0)}
            >
              <img
                src={`/storage/${guesthouse.image_path}`}
                alt={guesthouse.name}
                className="w-full h-full object-cover"
              />
            </div>
          ) : null}

          {guesthouse.description && (
            <div className="mb-8">
              <div 
                className="text-gray-700 leading-relaxed prose prose-sm sm:prose-base md:prose-lg max-w-none text-sm sm:text-base"
                dangerouslySetInnerHTML={{ __html: guesthouse.description }}
              />
            </div>
          )}

          {guesthouse.link && (
            <div>
              <a
                href={guesthouse.link}
                target="_blank"
                rel="noopener noreferrer"
                className="inline-flex items-center gap-2 bg-teal-600 text-white px-6 py-3 sm:px-8 sm:py-4 rounded-full text-sm sm:text-base font-bold hover:bg-teal-700 transition-colors"
              >
                前往官方網站
                <ExternalLink size={16} className="sm:w-[18px] sm:h-[18px]" />
              </a>
            </div>
          )}
        </div>
      </section>

      {/* Image Modal */}
      {selectedImageIndex !== null && (
        <div 
          className="fixed inset-0 bg-black/90 z-[100] flex items-center justify-center p-4 overflow-hidden"
          onClick={() => {
            setSelectedImageIndex(null);
            setIsZoomed(false);
          }}
        >
          {/* Close Button */}
          <button 
            className="absolute top-4 right-4 text-white/70 hover:text-white p-2 z-20"
            onClick={() => {
              setSelectedImageIndex(null);
              setIsZoomed(false);
            }}
          >
            <X size={32} />
          </button>

          {/* Previous Button */}
          {displayImages.length > 1 && (
            <button 
              className="absolute left-4 top-1/2 -translate-y-1/2 text-white/70 hover:text-white p-2 z-20 transition-colors bg-black/20 hover:bg-black/40 rounded-full"
              onClick={handlePrev}
            >
              <ChevronLeft size={40} />
            </button>
          )}

          {/* Next Button */}
          {displayImages.length > 1 && (
            <button 
              className="absolute right-4 top-1/2 -translate-y-1/2 text-white/70 hover:text-white p-2 z-20 transition-colors bg-black/20 hover:bg-black/40 rounded-full"
              onClick={handleNext}
            >
              <ChevronRight size={40} />
            </button>
          )}

          <img 
            src={displayImages[selectedImageIndex]} 
            alt="Full size" 
            className={`max-w-full max-h-[90vh] object-contain rounded-lg shadow-2xl transition-transform duration-300 ease-out select-none ${
              isZoomed ? 'cursor-zoom-out' : 'cursor-zoom-in'
            }`}
            style={{ 
              transform: isZoomed ? 'scale(2)' : 'scale(1)',
            }}
            onClick={(e) => {
              e.stopPropagation();
              setIsZoomed(!isZoomed);
            }}
            onMouseMove={(e) => {
              if (!isZoomed) return;
              const rect = e.currentTarget.getBoundingClientRect();
              const x = ((e.clientX - rect.left) / rect.width) * 100;
              const y = ((e.clientY - rect.top) / rect.height) * 100;
              e.currentTarget.style.transformOrigin = `${x}% ${y}%`;
            }}
          />
          
          {/* Image Counter */}
          {displayImages.length > 1 && (
            <div className="absolute bottom-6 left-1/2 -translate-x-1/2 text-white/80 bg-black/40 px-3 py-1 rounded-full text-sm font-medium z-20 select-none">
              {selectedImageIndex + 1} / {displayImages.length}
            </div>
          )}
        </div>
      )}
    </div>
  );
};

export default GuesthouseDetail;
