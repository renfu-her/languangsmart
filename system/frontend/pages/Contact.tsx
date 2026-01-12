
import React, { useState, useEffect } from 'react';
import { MapPin, Phone, MessageCircle } from 'lucide-react';
import SEO from '../components/SEO';
import { publicApi } from '../lib/api';

interface ContactInfoData {
  id: number;
  store_name: string;
  address: string | null;
  phone: string | null;
  line_id: string | null;
  sort_order: number;
  is_active: boolean;
}

const Contact: React.FC = () => {
  const [contactInfos, setContactInfos] = useState<ContactInfoData[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchContactInfos = async () => {
      try {
        const response = await publicApi.contactInfos.list();
        setContactInfos(response.data || []);
      } catch (error) {
        console.error('Failed to fetch contact infos:', error);
        setContactInfos([]);
      } finally {
        setLoading(false);
      }
    };

    fetchContactInfos();
  }, []);

  const structuredData = {
    '@context': 'https://schema.org',
    '@type': 'ContactPage',
    name: '聯絡我們 - 蘭光電動機車',
    description: '有任何問題或建議，歡迎透過以下方式與我們聯繫，我們將竭誠為您服務。',
    url: `${window.location.origin}/contact`,
    mainEntity: {
      '@type': 'LocalBusiness',
      name: '蘭光電動機車',
      telephone: '+886-8-861-0000',
      email: 'info@languang.com'
    }
  };

  return (
    <div className="animate-in fade-in duration-700">
      <SEO
        title="聯絡我們 - 蘭光電動機車"
        description="有任何問題或建議，歡迎透過以下方式與我們聯繫，我們將竭誠為您服務。"
        keywords="聯絡我們,客服,蘭光電動機車,小琉球租車聯絡"
        url="/contact"
        structuredData={structuredData}
      />
      <header className="py-12 sm:py-16 md:py-20 px-4 sm:px-6 bg-[#f0f4ff] text-center">
        <div className="max-w-4xl mx-auto">
          <p className="text-gray-400 tracking-[0.3em] uppercase mb-2 text-xs sm:text-sm">Contact Us</p>
          <h1 className="text-3xl sm:text-4xl md:text-5xl lg:text-6xl serif font-light mb-3 sm:mb-4">聯絡我們</h1>
          <p className="text-gray-500 max-w-xl mx-auto text-sm sm:text-base px-4">有任何問題或建議，歡迎透過以下方式與我們聯繫，我們將竭誠為您服務。</p>
          <div className="mt-3 sm:mt-4 text-xs text-gray-400">首頁 &gt; 聯絡我們</div>
        </div>
      </header>

      {loading ? (
        <section className="container mx-auto px-6 max-w-6xl py-12">
          <div className="flex justify-center items-center py-12">
            <div className="text-gray-400">載入中...</div>
          </div>
        </section>
      ) : contactInfos.length === 0 ? (
        <section className="container mx-auto px-6 max-w-6xl py-12">
          <div className="flex flex-col items-center justify-center py-20">
            <div className="text-gray-400 text-center">
              <p className="text-lg mb-2">目前沒有聯絡資訊</p>
            </div>
          </div>
        </section>
      ) : (
        <section className="container mx-auto px-4 sm:px-6 max-w-4xl py-8 sm:py-12">
          <div className="space-y-8 sm:space-y-12">
            {contactInfos.map((contactInfo) => (
              <div key={contactInfo.id} className="bg-white p-6 sm:p-8 md:p-10 lg:p-12 rounded-[30px] sm:rounded-[35px] md:rounded-[40px] shadow-sm border border-gray-100">
                <h3 className="text-2xl sm:text-3xl font-bold mb-6 sm:mb-8 serif">聯絡資訊</h3>
                <p className="text-xl sm:text-2xl font-bold text-gray-800 mb-6">{contactInfo.store_name}</p>
                <div className="space-y-4 sm:space-y-6">
                  {contactInfo.address && (
                    <div className="flex items-start gap-4">
                      <div className="flex-shrink-0 mt-1">
                        <MapPin size={24} className="text-teal-600" />
                      </div>
                      <div className="flex-1">
                        <p className="text-base font-bold text-gray-700 mb-1">地址</p>
                        <p className="text-lg text-gray-600">
                          <a 
                            href={`https://www.google.com.tw/maps/search/${encodeURIComponent(contactInfo.address)}`} 
                            target="_blank" 
                            rel="noopener noreferrer" 
                            className="hover:text-teal-600 transition-colors"
                          >
                            {contactInfo.address}
                          </a>
                        </p>
                      </div>
                    </div>
                  )}

                  {contactInfo.phone && (
                    <div className="flex items-start gap-4">
                      <div className="flex-shrink-0 mt-1">
                        <Phone size={24} className="text-teal-600" />
                      </div>
                      <div className="flex-1">
                        <p className="text-base font-bold text-gray-700 mb-1">電話</p>
                        <p className="text-lg text-gray-600">
                          <a href={`tel:${contactInfo.phone}`} className="hover:text-teal-600 transition-colors">
                            {contactInfo.phone}
                          </a>
                        </p>
                      </div>
                    </div>
                  )}

                  {contactInfo.line_id && (
                    <div className="flex items-start gap-4">
                      <div className="flex-shrink-0 mt-1">
                        <MessageCircle size={24} className="text-teal-600" />
                      </div>
                      <div className="flex-1">
                        <p className="text-base font-bold text-gray-700 mb-1">LINE ID</p>
                        <p className="text-lg text-gray-600">
                          <a 
                            href={`https://line.me/R/ti/p/${contactInfo.line_id.replace('@', '')}?oat_content=url&ts=01042332`} 
                            target="_blank" 
                            rel="noopener noreferrer" 
                            className="hover:text-teal-600 transition-colors"
                          >
                            {contactInfo.line_id}
                          </a>
                        </p>
                      </div>
                    </div>
                  )}
                </div>
              </div>
            ))}
          </div>
        </section>
      )}
    </div>
  );
};

export default Contact;
